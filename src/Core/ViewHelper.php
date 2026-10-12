<?php
declare(strict_types=1);

namespace PulsoCivico\Core;

/**
 * ViewHelper: Funciones de renderizado reutilizables para la UI y SEO.
 */
class ViewHelper
{
    /**
     * Renderiza badges oficiales de partidos políticos con logos vectoriales SVG.
     */
    public static function renderPartyBadge(string $partido): string
    {
        $p = trim($partido);
        $upper = strtoupper($p);

        if (str_contains($upper, 'MORENA')) {
            $icon = '<svg class="party-icon" viewBox="0 0 30 20" width="24" height="16" fill="none"><rect width="30" height="20" rx="4" fill="#8B1C3F"/><text x="15" y="14" fill="#FFFFFF" font-size="7" font-weight="900" font-family="system-ui, -apple-system, sans-serif" text-anchor="middle" letter-spacing="-0.3">morena</text></svg>';
            $label = 'Morena';
            $bg = '#8B1C3F';
            $color = '#FFFFFF';
        } elseif (str_contains($upper, 'PAN')) {
            $icon = '<svg class="party-icon" viewBox="0 0 20 20" width="16" height="16" fill="none"><rect width="20" height="20" rx="4" fill="#003893"/><circle cx="10" cy="10" r="7" fill="#FFFFFF"/><text x="10" y="12.5" fill="#003893" font-size="6.5" font-weight="900" font-family="system-ui, -apple-system, sans-serif" text-anchor="middle" letter-spacing="-0.4">PAN</text></svg>';
            $label = 'PAN';
            $bg = '#003893';
            $color = '#FFFFFF';
        } elseif (str_contains($upper, 'VERDE') || str_contains($upper, 'PVEM')) {
            $icon = '<svg class="party-icon" viewBox="0 0 20 20" width="16" height="16" fill="none"><rect width="20" height="20" rx="4" fill="#009E49"/><path d="M10 3 C13 6 16 9 16 13 C16 16.5 13 18.5 10 18.5 C7 18.5 4 16.5 4 13 C4 9 7 6 10 3 Z" fill="#FFFFFF" fill-opacity="0.3"/><text x="10" y="12.5" fill="#FFFFFF" font-size="5.5" font-weight="900" font-family="system-ui, -apple-system, sans-serif" text-anchor="middle">PVEM</text></svg>';
            $label = 'PVEM';
            $bg = '#009E49';
            $color = '#FFFFFF';
        } elseif (str_contains($upper, 'PRI')) {
            $hash = substr(md5($p), 0, 6);
            $icon = '<svg class="party-icon" viewBox="0 0 20 20" width="16" height="16" fill="none"><clipPath id="pri-h-' . $hash . '"><rect width="20" height="20" rx="4"/></clipPath><g clip-path="url(#pri-h-' . $hash . ')"><rect x="0" y="0" width="6.6" height="20" fill="#008037"/><rect x="6.6" y="0" width="6.8" height="20" fill="#FFFFFF"/><rect x="13.4" y="0" width="6.6" height="20" fill="#C8102E"/><text x="10" y="12.5" fill="#1C1C1E" font-size="6" font-weight="900" font-family="system-ui, -apple-system, sans-serif" text-anchor="middle">PRI</text></g></svg>';
            $label = 'PRI';
            $bg = '#C8102E';
            $color = '#FFFFFF';
        } elseif (str_contains($upper, 'PT') || str_contains($upper, 'TRABAJO')) {
            $icon = '<svg class="party-icon" viewBox="0 0 20 20" width="16" height="16" fill="none"><rect width="20" height="20" rx="4" fill="#DA291C"/><polygon points="10,2 11.5,6.5 16,6.5 12.5,9.5 14,14 10,11.5 6,14 7.5,9.5 4,6.5 8.5,6.5" fill="#FFCC00" fill-opacity="0.4"/><text x="10" y="13" fill="#FFFFFF" font-size="7" font-weight="900" font-family="system-ui, -apple-system, sans-serif" text-anchor="middle">PT</text></svg>';
            $label = 'PT';
            $bg = '#DA291C';
            $color = '#FFFFFF';
        } elseif (str_contains($upper, 'MC') || str_contains($upper, 'CIUDADANO')) {
            $icon = '<svg class="party-icon" viewBox="0 0 20 20" width="16" height="16" fill="none"><rect width="20" height="20" rx="4" fill="#FF8300"/><path d="M3 10 Q10 5 17 10 Q10 14 3 10 Z" fill="#FFFFFF" fill-opacity="0.3"/><text x="10" y="13" fill="#FFFFFF" font-size="7" font-weight="900" font-family="system-ui, -apple-system, sans-serif" text-anchor="middle">MC</text></svg>';
            $label = 'Movimiento Ciudadano';
            $bg = '#FF8300';
            $color = '#FFFFFF';
        } elseif (str_contains($upper, 'PES') || str_contains($upper, 'ENCUENTRO')) {
            $icon = '<svg class="party-icon" viewBox="0 0 20 20" width="16" height="16" fill="none"><rect width="20" height="20" rx="4" fill="#582C83"/><text x="10" y="12.5" fill="#FFFFFF" font-size="6" font-weight="900" font-family="system-ui, -apple-system, sans-serif" text-anchor="middle">PES</text></svg>';
            $label = 'PES';
            $bg = '#582C83';
            $color = '#FFFFFF';
        } elseif (str_contains($upper, 'PRD')) {
            $icon = '<svg class="party-icon" viewBox="0 0 20 20" width="16" height="16" fill="none"><rect width="20" height="20" rx="4" fill="#FFD100"/><text x="10" y="12.5" fill="#1C1C1E" font-size="6" font-weight="900" font-family="system-ui, -apple-system, sans-serif" text-anchor="middle">PRD</text></svg>';
            $label = 'PRD';
            $bg = '#FFD100';
            $color = '#1C1C1E';
        } else {
            $icon = '<svg class="party-icon" viewBox="0 0 20 20" width="16" height="16" fill="none"><rect width="20" height="20" rx="4" fill="#6C757D"/><circle cx="10" cy="8" r="3.5" fill="#FFFFFF"/><path d="M5 18 C5 14.5 7.5 13 10 13 C12.5 13 15 14.5 15 18 Z" fill="#FFFFFF"/></svg>';
            $label = $p !== '' ? $p : 'Independiente';
            $bg = '#6C757D';
            $color = '#FFFFFF';
        }

        return sprintf(
            '<span class="party-badge" title="%s" data-party-color="%s">%s<span>%s</span></span>',
            htmlspecialchars($label),
            $bg,
            $icon,
            htmlspecialchars($label)
        );
    }

    /**
     * Devuelve el color hexadecimal oficial de un partido.
     */
    public static function getPartyColor(string $partido): string
    {
        $upper = strtoupper(trim($partido));
        if (str_contains($upper, 'MORENA')) return '#8B1C3F';
        if (str_contains($upper, 'PAN')) return '#003893';
        if (str_contains($upper, 'VERDE') || str_contains($upper, 'PVEM')) return '#009E49';
        if (str_contains($upper, 'PRI')) return '#C8102E';
        if (str_contains($upper, 'PT') || str_contains($upper, 'TRABAJO')) return '#DA291C';
        if (str_contains($upper, 'MC') || str_contains($upper, 'CIUDADANO')) return '#FF8300';
        if (str_contains($upper, 'PES')) return '#582C83';
        if (str_contains($upper, 'PRD')) return '#FFD100';
        return '#6C757D';
    }

    /**
     * Sanitiza texto contra XSS.
     */
    public static function e(?string $text): string
    {
        return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
