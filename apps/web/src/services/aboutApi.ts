import { api } from '@/boot/axios'
import { extractResource } from '@/services/mappers'

export interface WeAreContent {
  id: number
  title: string
  titulo_hero: string | null
  mensaje_hero: string | null
  description: string | null
  vision: string | null
  mission: string | null
  values: string | null
}

export interface WeAreTeamImage {
  id: number
  path: string
  url: string
  title: string | null
  order: number
  published: boolean
}

export interface AboutPayload {
  weAre: WeAreContent
  teams: WeAreTeamImage[]
}

function mapTeam(item: Record<string, unknown>): WeAreTeamImage {
  return {
    id: Number(item.id),
    path: String(item.path ?? ''),
    url: String(item.url ?? ''),
    title: item.title ? String(item.title) : null,
    order: Number(item.order ?? 0),
    published: Boolean(item.published),
  }
}

export async function fetchAbout(): Promise<AboutPayload> {
  const { data } = await api.get('/public/we-are')
  const payload = extractResource<{
    we_are?: Record<string, unknown>
    teams?: Record<string, unknown>[]
  }>(data)

  const raw = payload.we_are ?? {}
  const weAre: WeAreContent = {
    id: Number(raw.id ?? 0),
    title: String(raw.title ?? 'Quiénes somos'),
    titulo_hero: raw.titulo_hero ? String(raw.titulo_hero).trim() || null : null,
    mensaje_hero: raw.mensaje_hero ? String(raw.mensaje_hero).trim() || null : null,
    description: raw.description ? String(raw.description) : null,
    vision: raw.vision ? String(raw.vision) : null,
    mission: raw.mission ? String(raw.mission) : null,
    values: raw.values ? String(raw.values) : null,
  }

  const teams = Array.isArray(payload.teams)
    ? payload.teams.map(mapTeam).filter((t) => t.url).sort((a, b) => a.order - b.order)
    : []

  return { weAre, teams }
}

export function hasRichText(value: string | null | undefined): boolean {
  if (!value) return false
  const text = value
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/\s+/g, ' ')
    .trim()
  return text.length > 0
}
