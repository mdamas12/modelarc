import type {
  ProcessStep,
  Project,
  ServiceItem,
  Testimonial,
  VirtualTour,
} from '@/types/models';

const img = {
  hero: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=2000&q=80',
  villa: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=1600&q=80',
  modern: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80',
  interior: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1600&q=80',
  kitchen: 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1600&q=80',
  living: 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1600&q=80',
  facade: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1600&q=80',
  pool: 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=1600&q=80',
  loft: 'https://images.unsplash.com/photo-1600573472592-401b489a3cdc?auto=format&fit=crop&w=1600&q=80',
  office: 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1600&q=80',
  before: 'https://images.unsplash.com/photo-1560184897-ae75f418493e?auto=format&fit=crop&w=1200&q=80',
  after: 'https://images.unsplash.com/photo-1600210492493-0946911123ea?auto=format&fit=crop&w=1200&q=80',
  serviceDesign: 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1000&q=80',
  serviceBuild: 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1000&q=80',
  serviceRemodel: 'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=1000&q=80',
};

const pano = {
  living: 'https://photo-sphere-viewer-data.js.org/assets/sphere.jpg',
  terrace: 'https://cdn.photo-sphere-viewer.fr/sphere/key-biscayne-1.jpg',
  lobby: 'https://cdn.photo-sphere-viewer.fr/sphere/bryce-canyon-1.jpg',
};

export const mockProjects: Project[] = [
  {
    id: '1',
    slug: 'casa-horizon',
    title: 'Casa Horizon',
    category: 'Residencial',
    location: 'Santo Domingo',
    year: 2025,
    coverImage: img.villa,
    images: [img.villa, img.interior, img.living, img.pool],
    description: 'Residencia contemporánea con volumetría limpia, luz natural y conexión total con el paisaje.',
    longDescription:
      'Casa Horizon es un proyecto residencial de lujo que equilibra minimalismo y calidez. Los espacios se abren hacia terrazas y jardines, mientras materiales nobles —piedra, madera y bronce— definen una atmósfera serena y atemporal.',
    hasVirtualTour: true,
    tourSlug: 'casa-horizon-360',
    beforeImage: img.before,
    afterImage: img.after,
    area: '480 m²',
    status: 'Completado',
  },
  {
    id: '2',
    slug: 'villa-aurora',
    title: 'Villa Aurora',
    category: 'Residencial',
    location: 'Punta Cana',
    year: 2024,
    coverImage: img.modern,
    images: [img.modern, img.kitchen, img.facade],
    description: 'Villa costera con líneas horizontales, patio central y acabados de alta gama.',
    longDescription:
      'Diseñada para el clima tropical, Villa Aurora integra ventilación cruzada, sombra controlada y una secuencia de espacios interiores-exteriores que celebran la vida al aire libre.',
    hasVirtualTour: true,
    tourSlug: 'villa-aurora-360',
    area: '620 m²',
    status: 'Completado',
  },
  {
    id: '3',
    slug: 'loft-atelier',
    title: 'Loft Atelier',
    category: 'Remodelación',
    location: 'Santiago',
    year: 2024,
    coverImage: img.loft,
    images: [img.loft, img.living, img.kitchen],
    description: 'Transformación integral de un loft industrial en un espacio habitable sofisticado.',
    hasVirtualTour: false,
    beforeImage: img.before,
    afterImage: img.after,
    area: '180 m²',
    status: 'Completado',
  },
  {
    id: '4',
    slug: 'oficina-norte',
    title: 'Oficina Norte',
    category: 'Comercial',
    location: 'Santo Domingo',
    year: 2023,
    coverImage: img.office,
    images: [img.office, img.interior],
    description: 'Espacio corporativo con identidad visual clara, zonas colaborativas y privacidad acústica.',
    hasVirtualTour: true,
    tourSlug: 'oficina-norte-360',
    area: '950 m²',
    status: 'Completado',
  },
  {
    id: '5',
    slug: 'residencia-selva',
    title: 'Residencia Selva',
    category: 'Residencial',
    location: 'Jarabacoa',
    year: 2025,
    coverImage: img.facade,
    images: [img.facade, img.pool, img.living],
    description: 'Arquitectura de montaña con materiales locales y vistas panorámicas al bosque.',
    hasVirtualTour: false,
    area: '350 m²',
    status: 'En construcción',
  },
  {
    id: '6',
    slug: 'penthouse-mira',
    title: 'Penthouse Mira',
    category: 'Remodelación',
    location: 'Santo Domingo',
    year: 2023,
    coverImage: img.interior,
    images: [img.interior, img.kitchen, img.living],
    description: 'Remodelación de penthouse con cocina abierta, terraza lounge y paleta monocromática.',
    hasVirtualTour: true,
    tourSlug: 'casa-horizon-360',
    area: '220 m²',
    status: 'Completado',
  },
];

export const mockTours: VirtualTour[] = [
  {
    id: 't1',
    slug: 'casa-horizon-360',
    title: 'Casa Horizon — Recorrido 360°',
    description: 'Explora la sala principal, terraza y acceso de esta residencia contemporánea.',
    coverImage: img.villa,
    projectSlug: 'casa-horizon',
    scenes: [
      {
        id: 'living',
        name: 'Sala principal',
        panoramaUrl: pano.living,
        thumbnailUrl: img.living,
        yaw: 0,
        pitch: 0,
        hotspots: [
          {
            id: 'to-terrace',
            yaw: 1.2,
            pitch: -0.05,
            label: 'Ir a terraza',
            targetSceneId: 'terrace',
          },
        ],
      },
      {
        id: 'terrace',
        name: 'Terraza',
        panoramaUrl: pano.terrace,
        thumbnailUrl: img.pool,
        yaw: 0.3,
        pitch: 0,
        hotspots: [
          {
            id: 'to-living',
            yaw: -1.1,
            pitch: 0,
            label: 'Volver a sala',
            targetSceneId: 'living',
          },
          {
            id: 'to-lobby',
            yaw: 0.8,
            pitch: -0.1,
            label: 'Acceso',
            targetSceneId: 'lobby',
          },
        ],
      },
      {
        id: 'lobby',
        name: 'Acceso',
        panoramaUrl: pano.lobby,
        thumbnailUrl: img.facade,
        yaw: 0,
        pitch: 0,
        hotspots: [
          {
            id: 'back-terrace',
            yaw: 2.0,
            pitch: 0,
            label: 'Terraza',
            targetSceneId: 'terrace',
          },
        ],
      },
    ],
  },
  {
    id: 't2',
    slug: 'villa-aurora-360',
    title: 'Villa Aurora — Recorrido 360°',
    description: 'Un paseo inmersivo por la villa costera y sus espacios abiertos.',
    coverImage: img.modern,
    projectSlug: 'villa-aurora',
    scenes: [
      {
        id: 'main',
        name: 'Salón',
        panoramaUrl: pano.living,
        thumbnailUrl: img.modern,
        yaw: 0.2,
        pitch: 0,
        hotspots: [],
      },
      {
        id: 'exterior',
        name: 'Exterior',
        panoramaUrl: pano.terrace,
        thumbnailUrl: img.facade,
        yaw: 0,
        pitch: 0,
        hotspots: [],
      },
    ],
  },
  {
    id: 't3',
    slug: 'oficina-norte-360',
    title: 'Oficina Norte — Recorrido 360°',
    description: 'Recorre el lobby y las zonas colaborativas del espacio corporativo.',
    coverImage: img.office,
    projectSlug: 'oficina-norte',
    scenes: [
      {
        id: 'lobby',
        name: 'Lobby',
        panoramaUrl: pano.lobby,
        thumbnailUrl: img.office,
        yaw: 0,
        pitch: 0,
        hotspots: [],
      },
    ],
  },
];

export const mockServices: ServiceItem[] = [
  {
    id: 'design',
    title: 'Diseño Arquitectónico',
    description:
      'Concepto, desarrollo y documentación de proyectos residenciales y comerciales con identidad propia.',
    image: img.serviceDesign,
  },
  {
    id: 'build',
    title: 'Construcción',
    description:
      'Ejecución integral con control de calidad, plazos claros y coordinación de especialidades.',
    image: img.serviceBuild,
  },
  {
    id: 'remodel',
    title: 'Remodelación',
    description:
      'Transformamos espacios existentes con precisión técnica y sensibilidad estética.',
    image: img.serviceRemodel,
  },
];

export const mockProcess: ProcessStep[] = [
  {
    number: '01',
    title: 'Consulta',
    description: 'Escuchamos tu visión, necesidades y presupuesto.',
  },
  {
    number: '02',
    title: 'Concepto',
    description: 'Definimos la idea rectora y el lenguaje espacial.',
  },
  {
    number: '03',
    title: 'Diseño',
    description: 'Desarrollamos planos, renders y materialidad.',
  },
  {
    number: '04',
    title: 'Planificación',
    description: 'Presupuesto, cronograma y logística de obra.',
  },
  {
    number: '05',
    title: 'Construcción',
    description: 'Ejecutamos con supervisión continua en sitio.',
  },
  {
    number: '06',
    title: 'Entrega',
    description: 'Cierre, recorrido final y acompañamiento post-obra.',
  },
];

export const mockTestimonials: Testimonial[] = [
  {
    id: '1',
    name: 'Ana Rodríguez',
    role: 'Propietaria — Casa Horizon',
    quote:
      'Modelarc entendió exactamente lo que queríamos. El resultado es un hogar sereno, luminoso y impecablemente ejecutado.',
  },
  {
    id: '2',
    name: 'Carlos Méndez',
    role: 'Director — Oficina Norte',
    quote:
      'Profesionalismo de punta a punta. Cumplieron plazos y el espacio corporativo superó nuestras expectativas.',
  },
  {
    id: '3',
    name: 'Laura Jiménez',
    role: 'Cliente — Remodelación',
    quote:
      'La remodelación transformó por completo nuestra vivienda. Comunicación clara y un ojo excepcional para el detalle.',
  },
];

export const heroImage = img.hero;
export const immersiveCover = img.interior;
