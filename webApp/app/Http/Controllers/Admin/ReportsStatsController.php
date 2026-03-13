<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Denuncias;
use App\Models\EntregasReciclaje;
use App\Models\HistorialEstadoDenuncia;
use App\Models\RutasProgramadas;
use App\Models\Zonas;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsStatsController extends Controller
{
    public function index(): View
    {
        $data = $this->buildReportData();
        $data['viewScope'] = 'admin';

        return view('admin.reports-stats', $data);
    }

    public function auditorIndex(): View
    {
        $data = $this->buildReportData();
        $data['viewScope'] = 'auditor';

        return view('admin.reports-stats', $data);
    }

    public function exportCsv(): StreamedResponse
    {
        $data = $this->buildReportData();
        $filename = 'reportes_estadisticas_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['Reporte', 'Campo', 'Valor']);
            fputcsv($output, ['General', 'Fecha de corte', Carbon::parse($data['fechaCorte'])->format('d/m/Y H:i')]);
            fputcsv($output, []);

            fputcsv($output, ['Recoleccion', 'Toneladas hoy', $data['kpisRecoleccion']['hoy']]);
            fputcsv($output, ['Recoleccion', 'Toneladas semana', $data['kpisRecoleccion']['semana']]);
            fputcsv($output, ['Recoleccion', 'Toneladas mes', $data['kpisRecoleccion']['mes']]);
            fputcsv($output, []);

            fputcsv($output, ['Recoleccion por zona', 'Zona', 'Toneladas']);
            foreach ($data['toneladasPorZona'] as $row) {
                fputcsv($output, ['Recoleccion por zona', (string) $row->zona, (float) $row->toneladas]);
            }
            fputcsv($output, []);

            fputcsv($output, ['Recoleccion por ruta', 'Ruta', 'Toneladas']);
            foreach ($data['toneladasPorRuta'] as $row) {
                fputcsv($output, ['Recoleccion por ruta', (string) $row->ruta, (float) $row->toneladas]);
            }
            fputcsv($output, []);

            fputcsv($output, ['Reciclaje por material', 'Material', 'Kg']);
            foreach ($data['reciclajePorMaterial'] as $row) {
                fputcsv($output, ['Reciclaje por material', (string) $row->material, (float) $row->kg_total]);
            }
            fputcsv($output, []);

            fputcsv($output, ['Puntos verdes mas activos', 'Punto verde', 'Kg reciclados']);
            foreach ($data['puntosMasActivos'] as $row) {
                fputcsv($output, ['Puntos verdes mas activos', (string) $row->punto_verde, (float) $row->kg_total]);
            }
            fputcsv($output, []);

            fputcsv($output, ['Comparativa materiales', 'Material', 'Porcentaje']);
            foreach ($data['comparativaMateriales'] as $row) {
                fputcsv($output, ['Comparativa materiales', (string) $row['material'], (float) $row['porcentaje'] . '%']);
            }
            fputcsv($output, []);

            fputcsv($output, ['Denuncias', 'Total', $data['denunciasResumen']['total']]);
            fputcsv($output, ['Denuncias', 'Atendidas/Cerradas', $data['denunciasResumen']['atendidas']]);
            fputcsv($output, ['Denuncias', 'Pendientes', $data['denunciasResumen']['pendientes']]);
            fputcsv($output, ['Denuncias', 'Promedio horas atencion', $data['denunciasResumen']['promedio_horas']]);
            fputcsv($output, []);

            fputcsv($output, ['Denuncias por estado', 'Estado', 'Cantidad']);
            foreach ($data['denunciasPorEstado'] as $row) {
                fputcsv($output, ['Denuncias por estado', (string) $row->estado, (int) $row->total]);
            }
            fputcsv($output, []);

            fputcsv($output, ['Zonas con mas denuncias', 'Zona', 'Cantidad']);
            foreach ($data['zonasMasDenuncias'] as $row) {
                fputcsv($output, ['Zonas con mas denuncias', (string) $row['zona'], (int) $row['total']]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildReportData(): array
    {
        $now = now();

        // -------------------------------------------------------------
        // Recoleccion estrategica
        // -------------------------------------------------------------
        $toneladasHoy = (float) RutasProgramadas::query()
            ->whereDate('fecha', $now->toDateString())
            ->sum('basura_recolectada_ton');

        $toneladasSemana = (float) RutasProgramadas::query()
            ->whereBetween('fecha', [
                Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString(),
                Carbon::now()->endOfWeek(Carbon::SUNDAY)->toDateString(),
            ])
            ->sum('basura_recolectada_ton');

        $toneladasMes = (float) RutasProgramadas::query()
            ->whereBetween('fecha', [
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString(),
            ])
            ->sum('basura_recolectada_ton');

        $toneladasPorZona = RutasProgramadas::query()
            ->join('rutas', 'rutas.id_ruta', '=', 'rutas_programadas.id_ruta')
            ->leftJoin('zonas', 'zonas.id_zona', '=', 'rutas.id_zona')
            ->selectRaw("COALESCE(zonas.nombre, 'Sin zona') as zona")
            ->selectRaw('SUM(COALESCE(rutas_programadas.basura_recolectada_ton, 0)) as toneladas')
            ->selectRaw('COUNT(*) as viajes')
            ->groupBy('zona')
            ->orderByDesc('toneladas')
            ->get();

        $toneladasPorRuta = RutasProgramadas::query()
            ->join('rutas', 'rutas.id_ruta', '=', 'rutas_programadas.id_ruta')
            ->select('rutas.nombre as ruta')
            ->selectRaw('SUM(COALESCE(rutas_programadas.basura_recolectada_ton, 0)) as toneladas')
            ->selectRaw('COUNT(*) as ejecuciones')
            ->groupBy('rutas.id_ruta', 'rutas.nombre')
            ->orderByDesc('toneladas')
            ->get();

        $recoleccionMensual = RutasProgramadas::query()
            ->whereDate('fecha', '>=', Carbon::now()->subMonths(11)->startOfMonth()->toDateString())
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as periodo")
            ->selectRaw('SUM(COALESCE(basura_recolectada_ton, 0)) as toneladas')
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $recoleccionAnual = RutasProgramadas::query()
            ->selectRaw('YEAR(fecha) as anio')
            ->selectRaw('SUM(COALESCE(basura_recolectada_ton, 0)) as toneladas')
            ->groupBy('anio')
            ->orderBy('anio')
            ->get();

        // -------------------------------------------------------------
        // Reciclaje estrategico
        // -------------------------------------------------------------
        $reciclajePorMaterial = EntregasReciclaje::query()
            ->join('contenedores', 'contenedores.id_contenedor', '=', 'entregas_reciclaje.id_contenedor')
            ->join('tipos_material', 'tipos_material.id_material', '=', 'contenedores.id_material')
            ->select('tipos_material.nombre as material')
            ->selectRaw('SUM(COALESCE(entregas_reciclaje.cantidad_kg, 0)) as kg_total')
            ->selectRaw('COUNT(*) as entregas')
            ->groupBy('tipos_material.id_material', 'tipos_material.nombre')
            ->orderByDesc('kg_total')
            ->get();

        $totalKgReciclado = (float) $reciclajePorMaterial->sum('kg_total');

        $puntosMasActivos = EntregasReciclaje::query()
            ->join('contenedores', 'contenedores.id_contenedor', '=', 'entregas_reciclaje.id_contenedor')
            ->join('puntos_verdes', 'puntos_verdes.id_punto_verde', '=', 'contenedores.id_punto_verde')
            ->select('puntos_verdes.nombre as punto_verde')
            ->selectRaw('SUM(COALESCE(entregas_reciclaje.cantidad_kg, 0)) as kg_total')
            ->selectRaw('COUNT(*) as entregas')
            ->groupBy('puntos_verdes.id_punto_verde', 'puntos_verdes.nombre')
            ->orderByDesc('kg_total')
            ->limit(10)
            ->get();

        $tendenciaCiudadana = EntregasReciclaje::query()
            ->whereDate('fecha', '>=', Carbon::now()->subMonths(11)->startOfMonth()->toDateString())
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as periodo")
            ->selectRaw('SUM(COALESCE(cantidad_kg, 0)) as kg_total')
            ->selectRaw('COUNT(DISTINCT ciudadano_codigo) as ciudadanos_activos')
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $comparativaMateriales = $reciclajePorMaterial
            ->map(function ($row) use ($totalKgReciclado) {
                $kg = (float) $row->kg_total;

                return [
                    'material' => (string) $row->material,
                    'kg_total' => $kg,
                    'porcentaje' => $totalKgReciclado > 0 ? round(($kg / $totalKgReciclado) * 100, 2) : 0.0,
                ];
            })
            ->values();

        // -------------------------------------------------------------
        // Denuncias estrategicas
        // -------------------------------------------------------------
        $denunciasPorEstado = Denuncias::query()
            ->selectRaw("LOWER(TRIM(estado)) as estado")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado')
            ->get();

        $totalDenuncias = (int) $denunciasPorEstado->sum('total');
        $atendidas = (int) $denunciasPorEstado
            ->whereIn('estado', ['atendida', 'cerrada'])
            ->sum('total');
        $pendientes = max($totalDenuncias - $atendidas, 0);

        $promedioAtencionHoras = $this->calculateAverageAttentionHours();

        $zonasMasDenuncias = $this->calculateComplaintHotZones();

        return [
            'kpisRecoleccion' => [
                'hoy' => round($toneladasHoy, 2),
                'semana' => round($toneladasSemana, 2),
                'mes' => round($toneladasMes, 2),
            ],
            'toneladasPorZona' => $toneladasPorZona,
            'toneladasPorRuta' => $toneladasPorRuta,
            'recoleccionMensual' => $recoleccionMensual,
            'recoleccionAnual' => $recoleccionAnual,
            'reciclajePorMaterial' => $reciclajePorMaterial,
            'puntosMasActivos' => $puntosMasActivos,
            'tendenciaCiudadana' => $tendenciaCiudadana,
            'comparativaMateriales' => $comparativaMateriales,
            'denunciasResumen' => [
                'total' => $totalDenuncias,
                'atendidas' => $atendidas,
                'pendientes' => $pendientes,
                'promedio_horas' => $promedioAtencionHoras,
                'promedio_dias' => round($promedioAtencionHoras / 24, 2),
            ],
            'denunciasPorEstado' => $denunciasPorEstado,
            'zonasMasDenuncias' => $zonasMasDenuncias,
            'fechaCorte' => $now,
        ];
    }

    private function calculateAverageAttentionHours(): float
    {
        $denuncias = Denuncias::query()
            ->select(['id_denuncia', 'fecha'])
            ->get()
            ->keyBy('id_denuncia');

        if ($denuncias->isEmpty()) {
            return 0.0;
        }

        $cierres = HistorialEstadoDenuncia::query()
            ->where(function ($query): void {
                $query->whereRaw("LOWER(TRIM(estado)) = 'atendida'")
                    ->orWhereRaw("LOWER(TRIM(estado)) = 'cerrada'");
            })
            ->orderBy('fecha')
            ->get()
            ->groupBy('id_denuncia')
            ->map(fn (Collection $rows) => $rows->first());

        $horas = [];

        foreach ($cierres as $idDenuncia => $cierre) {
            $denuncia = $denuncias->get($idDenuncia);
            if (!$denuncia || !$denuncia->fecha || !$cierre->fecha) {
                continue;
            }

            $inicio = Carbon::parse($denuncia->fecha);
            $fin = Carbon::parse($cierre->fecha);

            if ($fin->greaterThan($inicio)) {
                $horas[] = $inicio->diffInHours($fin);
            }
        }

        if (empty($horas)) {
            return 0.0;
        }

        return round(array_sum($horas) / count($horas), 2);
    }

    private function calculateComplaintHotZones(): Collection
    {
        $zonas = Zonas::query()
            ->select(['id_zona', 'nombre', 'latitud', 'longitud'])
            ->get()
            ->filter(fn ($zona) => $zona->latitud !== null && $zona->longitud !== null)
            ->values();

        if ($zonas->isEmpty()) {
            return collect();
        }

        $denuncias = Denuncias::query()
            ->select(['id_denuncia', 'latitud', 'longitud'])
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        $conteo = [];

        foreach ($denuncias as $denuncia) {
            $lat = (float) $denuncia->latitud;
            $lng = (float) $denuncia->longitud;

            $zonaCercana = $zonas->sortBy(function ($zona) use ($lat, $lng) {
                $dLat = $lat - (float) $zona->latitud;
                $dLng = $lng - (float) $zona->longitud;

                return ($dLat * $dLat) + ($dLng * $dLng);
            })->first();

            if (!$zonaCercana) {
                continue;
            }

            $nombreZona = (string) $zonaCercana->nombre;
            $conteo[$nombreZona] = ($conteo[$nombreZona] ?? 0) + 1;
        }

        return collect($conteo)
            ->map(fn ($total, $zona) => ['zona' => $zona, 'total' => $total])
            ->sortByDesc('total')
            ->values()
            ->take(10);
    }
}
