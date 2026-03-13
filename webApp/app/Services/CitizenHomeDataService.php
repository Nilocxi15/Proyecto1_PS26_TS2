<?php

namespace App\Services;

use App\Models\PuntosVerdes;
use App\Models\Rutas;
use App\Models\TiposMaterial;

class CitizenHomeDataService
{
    public function buildHomeData(): array
    {
        try {
            $tiposMaterial = TiposMaterial::query()
                ->orderBy('nombre')
                ->pluck('nombre')
                ->map(fn ($tipo) => $this->normalizeLabel($tipo, true))
                ->unique()
                ->values();

            $rutas = Rutas::query()
                ->with([
                    'dias:id_dia,nombre',
                    'coordenadas' => function ($query): void {
                        $query->orderBy('orden');
                    },
                ])
                ->orderBy('nombre')
                ->get();

            $greenPoints = PuntosVerdes::query()
                ->orderBy('nombre')
                ->get(['nombre', 'direccion', 'latitud', 'longitud', 'horario'])
                ->map(function ($punto): array {
                    return [
                        'nombre' => $punto->nombre ?: 'Punto verde',
                        'direccion' => $punto->direccion ?: 'Sin direccion',
                        'latitud' => (float) $punto->latitud,
                        'longitud' => (float) $punto->longitud,
                        'horario' => $punto->horario ?: 'Sin definir',
                    ];
                })
                ->filter(function (array $punto): bool {
                    return $punto['latitud'] !== 0.0 || $punto['longitud'] !== 0.0;
                })
                ->values()
                ->all();

            $horarios = collect();
            $mapRoutes = [];

            foreach ($rutas as $ruta) {
                $dias = $ruta->dias->pluck('nombre')->filter()->values();

                if ($dias->isEmpty()) {
                    $dias = collect(['Sin definir']);
                }

                foreach ($dias as $dia) {
                    $horarios->push([
                        'ruta' => $ruta->nombre ?: 'Ruta sin nombre',
                        'tipo_residuo' => $this->normalizeLabel($ruta->tipo_residuo, true),
                        'hora_inicio' => $ruta->horario_inicio ?: '--:--',
                        'hora_fin' => $ruta->horario_fin ?: '--:--',
                        'dia' => $this->normalizeLabel($dia, false),
                    ]);
                }

                $points = $ruta->coordenadas
                    ->map(function ($coord): array {
                        return [(float) $coord->latitud, (float) $coord->longitud];
                    })
                    ->filter(function (array $point): bool {
                        return $point[0] !== 0.0 || $point[1] !== 0.0;
                    })
                    ->values()
                    ->all();

                if (count($points) < 2 && $ruta->lat_inicio && $ruta->lon_inicio && $ruta->lat_fin && $ruta->lon_fin) {
                    $points = [
                        [(float) $ruta->lat_inicio, (float) $ruta->lon_inicio],
                        [(float) $ruta->lat_fin, (float) $ruta->lon_fin],
                    ];
                }

                if (count($points) >= 2) {
                    $mapRoutes[] = [
                        'nombre' => $ruta->nombre ?: 'Ruta sin nombre',
                        'tipo_residuo' => $this->normalizeLabel($ruta->tipo_residuo, true),
                        'puntos' => $points,
                    ];
                }
            }

            if ($horarios->isEmpty()) {
                $horarios = collect($this->defaultHorarios());
            }

            if (empty($mapRoutes)) {
                $mapRoutes = $this->defaultMapRoutes();
            }

            if (empty($greenPoints)) {
                $greenPoints = $this->defaultGreenPoints();
            }

            return compact('tiposMaterial', 'horarios', 'mapRoutes', 'greenPoints');
        } catch (\Throwable $exception) {
            return [
                'tiposMaterial' => collect(['Organico', 'Plastico', 'Papel y carton', 'Vidrio']),
                'horarios' => collect($this->defaultHorarios()),
                'mapRoutes' => $this->defaultMapRoutes(),
                'greenPoints' => $this->defaultGreenPoints(),
            ];
        }
    }

    private function normalizeLabel(?string $value, bool $titleCase = false): string
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return 'Sin definir';
        }

        $knownFixes = [
            'Mi├®rcoles' => 'Miercoles',
            'S├íbado' => 'Sabado',
            'MiÃ©rcoles' => 'Miercoles',
            'SÃ¡bado' => 'Sabado',
            'Miercoles' => 'Miercoles',
            'Sabado' => 'Sabado',
            'Organico' => 'Organico',
            'Plastico' => 'Plastico',
            'Papel y carton' => 'Papel y carton',
        ];

        $text = $knownFixes[$text] ?? $text;

        if ($titleCase) {
            $text = mb_convert_case(mb_strtolower($text, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        }

        return $text;
    }

    private function defaultHorarios(): array
    {
        return [
            ['ruta' => 'Zona Centro', 'tipo_residuo' => 'Organico', 'hora_inicio' => '08:00', 'hora_fin' => '12:00', 'dia' => 'Lunes'],
            ['ruta' => 'Zona Norte', 'tipo_residuo' => 'Plastico', 'hora_inicio' => '14:00', 'hora_fin' => '18:00', 'dia' => 'Miercoles'],
            ['ruta' => 'Zona Sur', 'tipo_residuo' => 'Papel y carton', 'hora_inicio' => '08:00', 'hora_fin' => '12:00', 'dia' => 'Viernes'],
            ['ruta' => 'Zona 5', 'tipo_residuo' => 'Vidrio', 'hora_inicio' => '13:00', 'hora_fin' => '16:00', 'dia' => 'Martes'],
        ];
    }

    private function defaultMapRoutes(): array
    {
        return [
            [
                'nombre' => 'Zona Centro',
                'tipo_residuo' => 'Organico',
                'puntos' => [[14.6349, -90.5069], [14.6401, -90.5002], [14.6468, -90.4975]],
            ],
            [
                'nombre' => 'Zona Norte',
                'tipo_residuo' => 'Plastico',
                'puntos' => [[14.6613, -90.5222], [14.669, -90.516], [14.6744, -90.5098]],
            ],
        ];
    }

    private function defaultGreenPoints(): array
    {
        return [
            [
                'nombre' => 'Punto Verde Centro',
                'direccion' => 'Zona 1, Centro Historico',
                'latitud' => 14.6373,
                'longitud' => -90.5135,
                'horario' => '08:00-17:00',
            ],
            [
                'nombre' => 'Punto Verde Norte',
                'direccion' => 'Zona 17, Boulevard Principal',
                'latitud' => 14.6684,
                'longitud' => -90.4979,
                'horario' => '09:00-18:00',
            ],
        ];
    }
}
