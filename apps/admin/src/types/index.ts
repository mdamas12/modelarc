export interface User {
  id: number
  name: string
  email: string
  status: string
  last_login_at?: string | null
  roles?: string[]
  created_at?: string
}

export interface MediaItem {
  id: number
  uuid?: string
  url: string
  path?: string
  original_name?: string
  mime_type?: string
  extension?: string
  size?: number
  width?: number
  height?: number
  type?: string
  category?: string | null
  subcategory?: string | null
  sort_order?: number
  is_published?: boolean
  created_at?: string
}

export interface GalleryChange {
  id: number
  project_id: number
  before_media_id: number
  design_media_id?: number | null
  after_media_id?: number | null
  compare_with: 'design' | 'after'
  compare_label?: string
  subcategory?: string | null
  title?: string | null
  description?: string | null
  sort_order?: number
  is_featured?: boolean
  before_media?: MediaItem | null
  design_media?: MediaItem | null
  after_media?: MediaItem | null
  comparison_media?: MediaItem | null
  comparison_image_url?: string | null
  before_image_url?: string | null
  created_at?: string
  updated_at?: string
}

export interface Project {
  id: number
  title: string
  slug: string
  summary?: string | null
  description?: string | null
  category: string
  location?: string | null
  year?: number | null
  status?: string | null
  area?: string | null
  duration?: string | null
  client_name?: string | null
  is_featured?: boolean
  sort_order?: number
  has_virtual_tour?: boolean
  publication_status?: string
  published_at?: string | null
  seo_title?: string | null
  seo_description?: string | null
  views_count?: number
  cover_media?: MediaItem | null
  project_media?: ProjectMedia[]
  gallery_changes?: GalleryChange[]
  virtual_tour?: VirtualTour | null
  created_at?: string
  updated_at?: string
}

export interface ProjectMedia {
  id?: number
  media_id: number
  type?: string
  title?: string | null
  description?: string | null
  subcategory?: string | null
  sort_order?: number
  is_cover?: boolean
  is_published?: boolean
  media?: MediaItem
}

export interface VirtualTour {
  id: number
  project_id: number
  name: string
  slug: string
  description?: string | null
  status?: string
  initial_scene_id?: number | null
  autorotate_enabled?: boolean
  autorotate_speed?: number
  show_compass?: boolean
  show_scene_selector?: boolean
  published_at?: string | null
  project?: Project | null
  initial_scene?: TourScene | null
  scenes?: TourScene[]
  created_at?: string
  updated_at?: string
}

export interface TourScene {
  id: number
  virtual_tour_id: number
  name: string
  slug?: string
  description?: string | null
  initial_yaw?: number
  initial_pitch?: number
  initial_zoom?: number
  sort_order?: number
  status?: string
  panorama_media?: MediaItem | null
  thumbnail_media?: MediaItem | null
  hotspots?: TourHotspot[]
}

export interface TourHotspot {
  id: number
  type: 'scene' | 'info' | 'media' | 'link' | string
  title?: string | null
  description?: string | null
  yaw: number
  pitch: number
  icon?: string | null
  target_scene_id?: number | null
  media_id?: number | null
  external_url?: string | null
  configuration?: Record<string, unknown> | null
  sort_order?: number
  status?: string
  media?: MediaItem | null
  target_scene?: TourScene | null
}

export interface Service {
  id: number
  name: string
  slug: string
  icon?: string | null
  summary?: string | null
  description?: string | null
  features?: string[] | null
  sort_order?: number
  status?: string
  image?: MediaItem | null
  created_at?: string
  updated_at?: string
}

export interface Testimonial {
  id: number
  client_name: string
  quote: string
  rating?: number | null
  sort_order?: number
  status?: string
  client_photo?: MediaItem | null
  project?: Project | null
  project_id?: number | null
  created_at?: string
}

export interface Lead {
  id: number
  name: string
  email: string
  phone?: string | null
  project_type?: string | null
  message?: string | null
  budget_range?: string | null
  preferred_contact_method?: string | null
  status: string
  source?: string | null
  project_id?: number | null
  project?: Project | null
  created_at?: string
  updated_at?: string
}

export interface SiteSetting {
  id: number
  key: string
  value: unknown
  updated_at?: string
}

export interface DashboardData {
  projects_total: number
  projects_published: number
  projects_featured: number
  projects_with_tour: number
  tours_published: number
  services_active: number
  testimonials_active: number
  leads_total: number
  leads_new: number
  leads_by_status: Record<string, number>
  projects_by_publication: Record<string, number>
  recent_leads: Lead[]
  recent_projects: Project[]
  // Extended mock fields for richer UI
  visits_total?: number
  storage_used_gb?: number
  storage_total_gb?: number
  storage_breakdown?: { label: string; value: number }[]
  activity?: { id: number; title: string; description: string; time: string }[]
  chart_visits?: { labels: string[]; data: number[] }
  top_projects?: { name: string; views: number }[]
}

export interface Paginated<T> {
  data: T[]
  meta?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  links?: unknown
}
