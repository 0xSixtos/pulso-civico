import csv
import io
import unittest
from unittest.mock import patch

from fastapi import FastAPI
from fastapi.testclient import TestClient

from app.routers import api


class ExploradorCsvTests(unittest.TestCase):
    def export_rows(self, count, filters=None):
        calls = []

        def consultar(**kwargs):
            calls.append(kwargs)
            offset = (kwargs["page"] - 1) * kwargs["limit"]
            end = min(offset + kwargs["limit"], count)
            return {
                "items": [{"nombre": f"Persona {i}"} for i in range(offset, end)],
                "has_more": end < count,
            }

        app = FastAPI()
        app.include_router(api.router, prefix="/api")
        params = {"columnas": "nombre", **(filters or {})}
        with patch.object(api.data_service, "consultar_explorador", side_effect=consultar):
            with TestClient(app) as client:
                response = client.get("/api/explorador/exportar.csv", params=params)
        self.assertEqual(response.status_code, 200)
        self.assertTrue(response.content.startswith(b"\xef\xbb\xbf"))
        self.assertIn("attachment", response.headers["content-disposition"])
        rows = list(csv.reader(io.StringIO(response.content.decode("utf-8-sig"))))
        return rows, calls

    def test_exports_every_page_in_order(self):
        for count in (5001, 10001):
            with self.subTest(count=count):
                rows, calls = self.export_rows(count)
                self.assertEqual(rows[0], ["Político / Funcionario"])
                self.assertEqual(rows[1:], [[f"Persona {i}"] for i in range(count)])
                self.assertEqual([c["page"] for c in calls], list(range(1, len(calls) + 1)))

    def test_preserves_all_filters_on_every_page(self):
        filters = {
            "q": "Persona", "nivel": "estatal", "cargo": "diputado_local",
            "entidad": "san-luis-potosi", "partido": "partido-prueba",
            "transicion": "sin_cambio", "principio": "mayoria",
            "licencia": "en_funciones", "reeleccion": "primer_periodo",
            "periodo": "historico", "educacion": "licenciatura",
        }
        rows, calls = self.export_rows(5001, filters)
        self.assertEqual(len(rows), 5002)
        self.assertEqual(len(calls), 2)
        for call in calls:
            self.assertEqual({key: call[key] for key in filters}, filters)

    def test_empty_export_contains_only_header(self):
        rows, calls = self.export_rows(0)
        self.assertEqual(rows, [["Político / Funcionario"]])
        self.assertEqual(len(calls), 1)

    def test_exact_page_size_does_not_request_extra_page(self):
        rows, calls = self.export_rows(5000)
        self.assertEqual(len(rows), 5001)
        self.assertEqual(len(calls), 1)


if __name__ == "__main__":
    unittest.main()
