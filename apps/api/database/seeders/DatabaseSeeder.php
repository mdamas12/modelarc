<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Media;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Models\ProjectType;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\TourHotspot;
use App\Models\TourScene;
use App\Models\User;
use App\Models\VirtualTour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::findOrCreate('superadmin', 'web');
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('editor', 'web');

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@modelarc.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles([$superAdminRole]);

        $types = collect([
            ['name' => 'Residencial', 'slug' => 'residencial', 'description' => 'Viviendas y desarrollos habitacionales'],
            ['name' => 'Comercial', 'slug' => 'comercial', 'description' => 'Locales, retail y espacios comerciales'],
            ['name' => 'Corporativo', 'slug' => 'corporativo', 'description' => 'Oficinas y sedes empresariales'],
        ])->map(fn (array $data) => ProjectType::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [...$data, 'status' => 'active']
        ));

        $services = [
            [
                'name' => 'Diseño',
                'slug' => 'diseno',
                'icon' => 'pen-tool',
                'summary' => 'Arquitectura y diseño conceptual a detalle.',
                'description' => 'Desarrollamos el concepto arquitectónico, planos y visualizaciones para materializar tu visión.',
                'features' => ['Anteproyecto', 'Planos arquitectónicos', 'Renders 3D', 'Asesoría normativa'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Construcción',
                'slug' => 'construccion',
                'icon' => 'hard-hat',
                'summary' => 'Ejecución integral de obra con control de calidad.',
                'description' => 'Gestionamos la construcción completa con supervisión técnica y seguimiento de avance.',
                'features' => ['Dirección de obra', 'Control de costos', 'Cronograma', 'Entrega llave en mano'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Remodelación',
                'slug' => 'remodelacion',
                'icon' => 'hammer',
                'summary' => 'Transformación de espacios existentes.',
                'description' => 'Renovamos y adaptamos propiedades para mejorar funcionalidad, estética y valor.',
                'features' => ['Diagnóstico', 'Diseño de interiores', 'Obra menor y mayor', 'Antes y después'],
                'sort_order' => 3,
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['slug' => $service['slug']],
                [...$service, 'status' => 'active']
            );
        }

        $coverMedia = Media::query()->updateOrCreate(
            ['uuid' => '00000000-0000-4000-8000-000000000001'],
            [
                'disk' => 'public',
                'path' => 'placeholders/casa-moderna.jpg',
                'original_name' => 'casa-moderna.jpg',
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'size' => 0,
                'width' => 1920,
                'height' => 1080,
                'type' => 'image',
                'created_by' => $admin->id,
                'variants' => null,
            ]
        );

        $panoLobby = Media::query()->updateOrCreate(
            ['uuid' => '00000000-0000-4000-8000-000000000010'],
            [
                'disk' => 'public',
                'path' => 'placeholders/pano-lobby.jpg',
                'original_name' => 'pano-lobby.jpg',
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'size' => 0,
                'width' => 8192,
                'height' => 4096,
                'type' => 'panorama',
                'created_by' => $admin->id,
            ]
        );

        $panoLiving = Media::query()->updateOrCreate(
            ['uuid' => '00000000-0000-4000-8000-000000000011'],
            [
                'disk' => 'public',
                'path' => 'placeholders/pano-living.jpg',
                'original_name' => 'pano-living.jpg',
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'size' => 0,
                'width' => 8192,
                'height' => 4096,
                'type' => 'panorama',
                'created_by' => $admin->id,
            ]
        );

        $panoTerrace = Media::query()->updateOrCreate(
            ['uuid' => '00000000-0000-4000-8000-000000000012'],
            [
                'disk' => 'public',
                'path' => 'placeholders/pano-terrace.jpg',
                'original_name' => 'pano-terrace.jpg',
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'size' => 0,
                'width' => 8192,
                'height' => 4096,
                'type' => 'panorama',
                'created_by' => $admin->id,
            ]
        );

        $projectsData = [
            [
                'title' => 'Casa Horizon',
                'slug' => 'casa-horizon',
                'summary' => 'Residencia contemporánea con amplios volúmenes y luz natural.',
                'description' => 'Proyecto residencial de dos niveles con patio central, terrazas y acabados de alta calidad.',
                'category' => 'residencial',
                'location' => 'Santiago, Chile',
                'year' => 2024,
                'status' => 'finalizado',
                'area' => '320 m²',
                'duration' => '14 meses',
                'client_name' => 'Familia Rojas',
                'is_featured' => true,
                'has_virtual_tour' => true,
                'publication_status' => 'published',
                'project_type_id' => $types->firstWhere('slug', 'residencial')->id,
            ],
            [
                'title' => 'Oficinas Norte Hub',
                'slug' => 'oficinas-norte-hub',
                'summary' => 'Espacio corporativo flexible para equipos híbridos.',
                'description' => 'Diseño de oficinas abiertas, salas de reunión y zonas de colaboración.',
                'category' => 'corporativo',
                'location' => 'Providencia, Santiago',
                'year' => 2025,
                'status' => 'en_ejecucion',
                'area' => '850 m²',
                'duration' => '10 meses',
                'client_name' => 'Norte Hub SpA',
                'is_featured' => true,
                'has_virtual_tour' => false,
                'publication_status' => 'published',
                'project_type_id' => $types->firstWhere('slug', 'corporativo')->id,
            ],
            [
                'title' => 'Boutique Galería 12',
                'slug' => 'boutique-galeria-12',
                'summary' => 'Remodelación comercial con recorrido experiencial.',
                'description' => 'Intervención de local retail con foco en iluminación y exhibición de producto.',
                'category' => 'comercial',
                'location' => 'Vitacura, Santiago',
                'year' => 2023,
                'status' => 'finalizado',
                'area' => '180 m²',
                'duration' => '5 meses',
                'client_name' => 'Galería 12',
                'is_featured' => false,
                'has_virtual_tour' => false,
                'publication_status' => 'published',
                'project_type_id' => $types->firstWhere('slug', 'comercial')->id,
            ],
        ];

        $casaHorizon = null;

        foreach ($projectsData as $data) {
            $project = Project::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'cover_media_id' => $coverMedia->id,
                    'published_at' => now()->subDays(rand(5, 60)),
                    'seo_title' => $data['title'].' | Modelarc',
                    'seo_description' => $data['summary'],
                    'views_count' => rand(20, 400),
                    'created_by' => $admin->id,
                ]
            );

            ProjectMedia::query()->updateOrCreate(
                [
                    'project_id' => $project->id,
                    'media_id' => $coverMedia->id,
                    'type' => 'gallery',
                ],
                [
                    'title' => 'Portada',
                    'sort_order' => 0,
                    'is_cover' => true,
                ]
            );

            if ($project->slug === 'casa-horizon') {
                $casaHorizon = $project;
            }
        }

        if ($casaHorizon) {
            $tour = VirtualTour::query()->updateOrCreate(
                ['slug' => 'casa-horizon-360'],
                [
                    'project_id' => $casaHorizon->id,
                    'name' => 'Tour Casa Horizon 360°',
                    'description' => 'Recorre el lobby, living y terraza de Casa Horizon.',
                    'status' => 'published',
                    'autorotate_enabled' => true,
                    'autorotate_speed' => 0.5,
                    'show_compass' => true,
                    'show_scene_selector' => true,
                    'published_at' => now(),
                ]
            );

            $sceneLobby = TourScene::query()->updateOrCreate(
                ['virtual_tour_id' => $tour->id, 'slug' => 'lobby'],
                [
                    'name' => 'Lobby',
                    'panorama_media_id' => $panoLobby->id,
                    'thumbnail_media_id' => $coverMedia->id,
                    'description' => 'Acceso principal',
                    'initial_yaw' => 0,
                    'initial_pitch' => 0,
                    'initial_zoom' => 75,
                    'sort_order' => 0,
                    'status' => 'active',
                ]
            );

            $sceneLiving = TourScene::query()->updateOrCreate(
                ['virtual_tour_id' => $tour->id, 'slug' => 'living'],
                [
                    'name' => 'Living',
                    'panorama_media_id' => $panoLiving->id,
                    'thumbnail_media_id' => $coverMedia->id,
                    'description' => 'Estar principal',
                    'initial_yaw' => 45,
                    'initial_pitch' => -5,
                    'initial_zoom' => 70,
                    'sort_order' => 1,
                    'status' => 'active',
                ]
            );

            $sceneTerrace = TourScene::query()->updateOrCreate(
                ['virtual_tour_id' => $tour->id, 'slug' => 'terraza'],
                [
                    'name' => 'Terraza',
                    'panorama_media_id' => $panoTerrace->id,
                    'thumbnail_media_id' => $coverMedia->id,
                    'description' => 'Terraza exterior',
                    'initial_yaw' => 90,
                    'initial_pitch' => -10,
                    'initial_zoom' => 80,
                    'sort_order' => 2,
                    'status' => 'active',
                ]
            );

            $tour->update(['initial_scene_id' => $sceneLobby->id]);

            TourHotspot::query()->updateOrCreate(
                [
                    'tour_scene_id' => $sceneLobby->id,
                    'title' => 'Ir al Living',
                    'type' => 'scene',
                ],
                [
                    'description' => 'Continuar al living',
                    'yaw' => 30,
                    'pitch' => 0,
                    'icon' => 'arrow',
                    'target_scene_id' => $sceneLiving->id,
                    'sort_order' => 0,
                    'status' => 'active',
                ]
            );

            TourHotspot::query()->updateOrCreate(
                [
                    'tour_scene_id' => $sceneLiving->id,
                    'title' => 'Ir a Terraza',
                    'type' => 'scene',
                ],
                [
                    'description' => 'Salir a la terraza',
                    'yaw' => 120,
                    'pitch' => -2,
                    'icon' => 'arrow',
                    'target_scene_id' => $sceneTerrace->id,
                    'sort_order' => 0,
                    'status' => 'active',
                ]
            );

            TourHotspot::query()->updateOrCreate(
                [
                    'tour_scene_id' => $sceneLiving->id,
                    'title' => 'Mobiliario a medida',
                    'type' => 'info',
                ],
                [
                    'description' => 'Diseño de muebles empotrados en roble natural.',
                    'yaw' => -40,
                    'pitch' => -8,
                    'icon' => 'info',
                    'sort_order' => 1,
                    'status' => 'active',
                    'configuration' => ['popup' => true],
                ]
            );
        }

        Testimonial::query()->updateOrCreate(
            ['client_name' => 'Camila Rojas', 'quote' => 'Modelarc transformó nuestra casa en un espacio luminoso y funcional. El proceso fue claro de principio a fin.'],
            [
                'project_id' => $casaHorizon?->id,
                'rating' => 5,
                'sort_order' => 1,
                'status' => 'active',
            ]
        );

        Testimonial::query()->updateOrCreate(
            ['client_name' => 'Andrés Vega', 'quote' => 'Excelente coordinación en la remodelación de nuestro local. Cumplieron plazos y la calidad se nota.'],
            [
                'rating' => 5,
                'sort_order' => 2,
                'status' => 'active',
            ]
        );

        Lead::query()->updateOrCreate(
            ['email' => 'prospecto@ejemplo.com', 'name' => 'María Pérez'],
            [
                'phone' => '+56 9 1234 5678',
                'project_type' => 'residencial',
                'message' => 'Quisiera cotizar el diseño de una casa de 250 m².',
                'budget_range' => '100-200M CLP',
                'preferred_contact_method' => 'whatsapp',
                'status' => 'new',
                'source' => 'website',
                'project_id' => $casaHorizon?->id,
            ]
        );

        $settings = [
            'site_name' => ['value' => 'Modelarc'],
            'site_tagline' => ['value' => 'Arquitectura, construcción y experiencias 360°'],
            'contact_email' => ['value' => 'hola@modelarc.com'],
            'contact_phone' => ['value' => '+56 2 2345 6789'],
        ];

        foreach ($settings as $key => $payload) {
            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $payload['value']]
            );
        }
    }
}
