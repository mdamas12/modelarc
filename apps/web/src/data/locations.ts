/** Local cascading location data for the contact form (no external API). */

export const COUNTRIES: string[] = [
  'Venezuela',
  'Colombia',
  'México',
  'Estados Unidos',
  'España',
  'Panamá',
  'Argentina',
  'Chile',
  'Perú',
  'Ecuador',
  'Brasil',
  'Uruguay',
  'Paraguay',
  'Bolivia',
  'Costa Rica',
  'República Dominicana',
  'Cuba',
  'Guatemala',
  'Honduras',
  'El Salvador',
  'Nicaragua',
  'Canadá',
  'Reino Unido',
  'Alemania',
  'Francia',
  'Italia',
  'Portugal',
  'Otro',
]

/** country -> state -> cities */
export const LOCATION_TREE: Record<string, Record<string, string[]>> = {
  Venezuela: {
    Amazonas: ['Puerto Ayacucho', 'San Fernando de Atabapo', 'Otro'],
    Anzoátegui: [
      'Barcelona',
      'Puerto La Cruz',
      'Lechería',
      'El Tigre',
      'Anaco',
      'Cantaura',
      'Otro',
    ],
    Apure: ['San Fernando de Apure', 'Guasdualito', 'Otro'],
    Aragua: ['Maracay', 'Turmero', 'La Victoria', 'Cagua', 'El Limón', 'Otro'],
    Barinas: ['Barinas', 'Socopó', 'Otro'],
    Bolívar: [
      'Ciudad Bolívar',
      'Puerto Ordaz',
      'San Félix',
      'Ciudad Guayana',
      'Upata',
      'Otro',
    ],
    Carabobo: ['Valencia', 'Puerto Cabello', 'Guacara', 'Naguanagua', 'Otro'],
    Cojedes: ['San Carlos', 'Tinaquillo', 'Otro'],
    'Delta Amacuro': ['Tucupita', 'Otro'],
    'Distrito Capital': ['Caracas', 'Otro'],
    Falcón: ['Coro', 'Punto Fijo', 'Otro'],
    Guárico: ['San Juan de los Morros', 'Calabozo', 'Valle de la Pascua', 'Otro'],
    Lara: ['Barquisimeto', 'Cabudare', 'Carora', 'El Tocuyo', 'Otro'],
    Mérida: ['Mérida', 'El Vigía', 'Ejido', 'Otro'],
    Miranda: [
      'Los Teques',
      'Guarenas',
      'Guatire',
      'Petare',
      'Charallave',
      'Ocumare del Tuy',
      'Otro',
    ],
    Monagas: ['Maturín', 'Punta de Mata', 'Otro'],
    'Nueva Esparta': ['Porlamar', 'La Asunción', 'Juan Griego', 'Otro'],
    Portuguesa: ['Guanare', 'Acarigua', 'Araure', 'Otro'],
    Sucre: ['Cumaná', 'Carúpano', 'Otro'],
    Táchira: ['San Cristóbal', 'Táriba', 'Rubio', 'Otro'],
    Trujillo: ['Trujillo', 'Valera', 'Boconó', 'Otro'],
    Vargas: ['La Guaira', 'Maiquetía', 'Catia La Mar', 'Otro'],
    Yaracuy: ['San Felipe', 'Yaritagua', 'Otro'],
    Zulia: ['Maracaibo', 'Cabimas', 'Ciudad Ojeda', 'San Francisco', 'Otro'],
  },
  Colombia: {
    'Bogotá D.C.': ['Bogotá', 'Otro'],
    Antioquia: ['Medellín', 'Envigado', 'Bello', 'Otro'],
    Atlántico: ['Barranquilla', 'Soledad', 'Otro'],
    'Valle del Cauca': ['Cali', 'Palmira', 'Otro'],
    Santander: ['Bucaramanga', 'Floridablanca', 'Otro'],
    Bolívar: ['Cartagena', 'Otro'],
    Otro: ['Otro'],
  },
  México: {
    'Ciudad de México': ['Ciudad de México', 'Otro'],
    Jalisco: ['Guadalajara', 'Zapopan', 'Otro'],
    'Nuevo León': ['Monterrey', 'San Pedro Garza García', 'Otro'],
    Otro: ['Otro'],
  },
  'Estados Unidos': {
    Florida: ['Miami', 'Orlando', 'Tampa', 'Otro'],
    Texas: ['Houston', 'Dallas', 'Austin', 'Otro'],
    California: ['Los Ángeles', 'San Diego', 'Otro'],
    'New York': ['New York', 'Otro'],
    Otro: ['Otro'],
  },
  España: {
    Madrid: ['Madrid', 'Otro'],
    Cataluña: ['Barcelona', 'Otro'],
    Valencia: ['Valencia', 'Otro'],
    Andalucía: ['Sevilla', 'Málaga', 'Otro'],
    Otro: ['Otro'],
  },
  Panamá: {
    Panamá: ['Ciudad de Panamá', 'Otro'],
    Colón: ['Colón', 'Otro'],
    Otro: ['Otro'],
  },
}

export function statesFor(country: string): string[] {
  const tree = LOCATION_TREE[country]
  if (!tree) return []
  return Object.keys(tree).sort((a, b) => a.localeCompare(b, 'es'))
}

export function citiesFor(country: string, state: string): string[] {
  const cities = LOCATION_TREE[country]?.[state]
  return cities ? [...cities] : []
}

export function hasMappedStates(country: string): boolean {
  return Boolean(LOCATION_TREE[country])
}
