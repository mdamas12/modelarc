import { api } from '@/boot/axios'
import type {
  DashboardData,
  GalleryChange,
  Lead,
  MediaItem,
  Paginated,
  Project,
  Service,
  SiteSetting,
  Testimonial,
  TourHotspot,
  TourScene,
  User,
  VirtualTour,
  WeAre,
  WeArePayload,
  WeAreTeam,
} from '@/types'

function unwrapData<T>(payload: { data: T } | T): T {
  if (payload && typeof payload === 'object' && 'data' in payload) {
    return (payload as { data: T }).data
  }
  return payload as T
}

export const adminApi = {
  async dashboard(): Promise<DashboardData> {
    const { data } = await api.get('/admin/dashboard')
    return unwrapData(data)
  },

  async projects(params?: Record<string, unknown>) {
    const { data } = await api.get<Paginated<Project>>('/admin/projects', { params })
    return data
  },

  async project(id: number | string) {
    const { data } = await api.get(`/admin/projects/${id}`)
    return unwrapData<Project>(data)
  },

  async createProject(payload: Record<string, unknown>) {
    const { data } = await api.post('/admin/projects', payload)
    return unwrapData<Project>(data)
  },

  async updateProject(id: number | string, payload: Record<string, unknown>) {
    const { data } = await api.put(`/admin/projects/${id}`, payload)
    return unwrapData<Project>(data)
  },

  async deleteProject(id: number | string) {
    await api.delete(`/admin/projects/${id}`)
  },

  async publishProject(id: number | string) {
    const { data } = await api.post(`/admin/projects/${id}/publish`)
    return unwrapData<Project>(data)
  },

  async archiveProject(id: number | string) {
    const { data } = await api.post(`/admin/projects/${id}/archive`)
    return unwrapData<Project>(data)
  },

  async reorderProjects(ids: number[]) {
    const { data } = await api.post('/admin/projects/reorder', { ids })
    return unwrapData<{ message: string }>(data)
  },

  async moveProject(id: number | string, direction: 'up' | 'down') {
    const { data } = await api.post(`/admin/projects/${id}/move`, { direction })
    return unwrapData<Project>(data)
  },

  async galleryChanges(projectId: number | string) {
    const { data } = await api.get(`/admin/projects/${projectId}/gallery-changes`)
    return unwrapData<GalleryChange[]>(data)
  },

  async createGalleryChange(projectId: number | string, payload: Record<string, unknown>) {
    const { data } = await api.post(`/admin/projects/${projectId}/gallery-changes`, payload)
    return unwrapData<GalleryChange>(data)
  },

  async updateGalleryChange(
    projectId: number | string,
    id: number | string,
    payload: Record<string, unknown>,
  ) {
    const { data } = await api.put(`/admin/projects/${projectId}/gallery-changes/${id}`, payload)
    return unwrapData<GalleryChange>(data)
  },

  async deleteGalleryChange(projectId: number | string, id: number | string) {
    await api.delete(`/admin/projects/${projectId}/gallery-changes/${id}`)
  },

  async reorderGalleryChanges(projectId: number | string, ids: number[]) {
    const { data } = await api.post(`/admin/projects/${projectId}/gallery-changes/reorder`, { ids })
    return unwrapData<{ message: string }>(data)
  },

  async media(params?: Record<string, unknown>) {
    const { data } = await api.get<Paginated<MediaItem>>('/admin/media', { params })
    return data
  },

  async uploadMedia(
    file: File,
    type = 'image',
    meta?: { category?: string | null; subcategory?: string | null; is_published?: boolean },
  ) {
    const form = new FormData()
    form.append('file', file)
    form.append('type', type)
    if (meta?.category) form.append('category', meta.category)
    if (meta?.subcategory) form.append('subcategory', meta.subcategory)
    if (meta?.is_published != null) form.append('is_published', meta.is_published ? '1' : '0')
    const { data } = await api.post('/admin/media', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
      // Panoramas de alta calidad pueden superar 40–80 MB
      timeout: 10 * 60 * 1000,
    })
    return unwrapData<MediaItem>(data)
  },

  async updateMedia(id: number | string, payload: Record<string, unknown>) {
    const { data } = await api.put(`/admin/media/${id}`, payload)
    return unwrapData<MediaItem>(data)
  },

  async reorderMedia(ids: number[]) {
    const { data } = await api.post('/admin/media/reorder', { ids })
    return unwrapData<{ message: string }>(data)
  },

  async deleteMedia(id: number | string) {
    await api.delete(`/admin/media/${id}`)
  },

  async tours(params?: Record<string, unknown>) {
    const { data } = await api.get<Paginated<VirtualTour>>('/admin/tours', { params })
    return data
  },

  async tour(id: number | string) {
    const { data } = await api.get(`/admin/tours/${id}`)
    return unwrapData<VirtualTour>(data)
  },

  async createTour(payload: Record<string, unknown>) {
    const { data } = await api.post('/admin/tours', payload)
    return unwrapData<VirtualTour>(data)
  },

  async updateTour(id: number | string, payload: Record<string, unknown>) {
    const { data } = await api.put(`/admin/tours/${id}`, payload)
    return unwrapData<VirtualTour>(data)
  },

  async deleteTour(id: number | string) {
    await api.delete(`/admin/tours/${id}`)
  },

  async publishTour(id: number | string) {
    const { data } = await api.post(`/admin/tours/${id}/publish`)
    return unwrapData<VirtualTour>(data)
  },

  async createScene(tourId: number | string, payload: Record<string, unknown>) {
    const { data } = await api.post(`/admin/tours/${tourId}/scenes`, payload)
    return unwrapData<TourScene>(data)
  },

  async updateScene(sceneId: number | string, payload: Record<string, unknown>) {
    const { data } = await api.put(`/admin/scenes/${sceneId}`, payload)
    return unwrapData<TourScene>(data)
  },

  async deleteScene(sceneId: number | string) {
    await api.delete(`/admin/scenes/${sceneId}`)
  },

  async createHotspot(sceneId: number | string, payload: Record<string, unknown>) {
    const { data } = await api.post(`/admin/scenes/${sceneId}/hotspots`, payload)
    return unwrapData<TourHotspot>(data)
  },

  async updateHotspot(hotspotId: number | string, payload: Record<string, unknown>) {
    const { data } = await api.put(`/admin/hotspots/${hotspotId}`, payload)
    return unwrapData<TourHotspot>(data)
  },

  async deleteHotspot(hotspotId: number | string) {
    await api.delete(`/admin/hotspots/${hotspotId}`)
  },

  async leads(params?: Record<string, unknown>) {
    const { data } = await api.get<Paginated<Lead>>('/admin/leads', { params })
    return data
  },

  async updateLead(id: number | string, payload: Record<string, unknown>) {
    const { data } = await api.put(`/admin/leads/${id}`, payload)
    return unwrapData<Lead>(data)
  },

  async deleteLead(id: number | string) {
    await api.delete(`/admin/leads/${id}`)
  },

  async services() {
    const { data } = await api.get('/admin/services')
    return unwrapData<Service[]>(data)
  },

  async createService(payload: Record<string, unknown>) {
    const { data } = await api.post('/admin/services', payload)
    return unwrapData<Service>(data)
  },

  async updateService(id: number | string, payload: Record<string, unknown>) {
    const { data } = await api.put(`/admin/services/${id}`, payload)
    return unwrapData<Service>(data)
  },

  async deleteService(id: number | string) {
    await api.delete(`/admin/services/${id}`)
  },

  async weAre() {
    const { data } = await api.get('/admin/we-are')
    return unwrapData<WeArePayload>(data)
  },

  async updateWeAre(payload: Record<string, unknown>) {
    const { data } = await api.put('/admin/we-are', payload)
    return unwrapData<WeAre>(data)
  },

  async createWeAreTeam(form: FormData) {
    const { data } = await api.post('/admin/we-are/team', form, {
      timeout: 5 * 60 * 1000,
    })
    return unwrapData<WeAreTeam>(data)
  },

  async updateWeAreTeam(id: number | string, form: FormData) {
    const { data } = await api.post(`/admin/we-are/team/${id}`, form, {
      timeout: 5 * 60 * 1000,
    })
    return unwrapData<WeAreTeam>(data)
  },

  async reorderWeAreTeam(ids: number[]) {
    const { data } = await api.post('/admin/we-are/team/reorder', { ids })
    return unwrapData<{ message: string }>(data)
  },

  async deleteWeAreTeam(id: number | string) {
    await api.delete(`/admin/we-are/team/${id}`)
  },

  async testimonials() {
    const { data } = await api.get('/admin/testimonials')
    return unwrapData<Testimonial[]>(data)
  },

  async createTestimonial(payload: Record<string, unknown>) {
    const { data } = await api.post('/admin/testimonials', payload)
    return unwrapData<Testimonial>(data)
  },

  async updateTestimonial(id: number | string, payload: Record<string, unknown>) {
    const { data } = await api.put(`/admin/testimonials/${id}`, payload)
    return unwrapData<Testimonial>(data)
  },

  async deleteTestimonial(id: number | string) {
    await api.delete(`/admin/testimonials/${id}`)
  },

  async testimonialInvitations(params?: Record<string, unknown>) {
    const { data } = await api.get('/admin/testimonial-invitations', { params })
    return data as Paginated<{
      id: number
      token?: string
      client_name: string
      client_email?: string
      status: string
      project?: { id: number; title: string } | null
      public_url?: string
      sent_at?: string | null
      completed_at?: string | null
    }>
  },

  async createTestimonialInvitation(payload: {
    project_id: number
    client_name: string
    client_email: string
  }) {
    const { data } = await api.post('/admin/testimonial-invitations', payload)
    return data as {
      data: {
        id: number
        token?: string
        public_url?: string
        client_name: string
        client_email?: string
        status: string
      }
      meta?: {
        mail_sent?: boolean
        mail_error?: string | null
        public_url?: string
      }
    }
  },

  async resendTestimonialInvitation(id: number | string) {
    const { data } = await api.post(`/admin/testimonial-invitations/${id}/resend`)
    return data as {
      data: unknown
      meta?: {
        mail_sent?: boolean
        mail_error?: string | null
        public_url?: string
      }
    }
  },

  async deleteTestimonialInvitation(id: number | string) {
    await api.delete(`/admin/testimonial-invitations/${id}`)
  },

  async settings() {
    const { data } = await api.get('/admin/settings')
    return unwrapData<SiteSetting[]>(data)
  },

  async upsertSetting(key: string, value: unknown) {
    const { data } = await api.post('/admin/settings', { key, value })
    return unwrapData<SiteSetting>(data)
  },

  async deleteSetting(id: number | string) {
    await api.delete(`/admin/settings/${id}`)
  },

  async users(params?: Record<string, unknown>) {
    const { data } = await api.get('/admin/users', { params })
    return data as Paginated<User>
  },

  async inviteUser(payload: { name: string; email: string; role: string }) {
    const { data } = await api.post('/admin/users', payload)
    return data as {
      data: User
      meta?: {
        mail_sent?: boolean
        mail_error?: string | null
        message?: string
      }
    }
  },

  async updateUser(id: number | string, payload: { name?: string; email?: string; role?: string }) {
    const { data } = await api.put(`/admin/users/${id}`, payload)
    return unwrapData<User>(data)
  },

  async blockUser(id: number | string) {
    const { data } = await api.post(`/admin/users/${id}/block`)
    return unwrapData<User>(data)
  },

  async unblockUser(id: number | string) {
    const { data } = await api.post(`/admin/users/${id}/unblock`)
    return unwrapData<User>(data)
  },

  async resendUserActivation(id: number | string) {
    const { data } = await api.post(`/admin/users/${id}/resend-activation`)
    return data as {
      data: User
      meta?: { mail_sent?: boolean; mail_error?: string | null; message?: string }
    }
  },

  async resetUserPassword(id: number | string) {
    const { data } = await api.post(`/admin/users/${id}/reset-password`)
    return data as {
      data: User
      meta?: { mail_sent?: boolean; mail_error?: string | null; message?: string }
    }
  },

  async getActivation(token: string) {
    const { data } = await api.get(`/admin/account/activation/${token}`)
    return unwrapData<{ name: string; email: string; expires_at?: string }>(data)
  },

  async activateAccount(
    token: string,
    payload: { password: string; password_confirmation: string },
  ) {
    const { data } = await api.post(`/admin/account/activation/${token}`, payload)
    return unwrapData<{ message: string; login_url?: string }>(data)
  },

  async getPasswordReset(token: string) {
    const { data } = await api.get(`/admin/account/password-reset/${token}`)
    return unwrapData<{ name: string; email: string; expires_at?: string }>(data)
  },

  async completePasswordReset(
    token: string,
    payload: { password: string; password_confirmation: string },
  ) {
    const { data } = await api.post(`/admin/account/password-reset/${token}`, payload)
    return unwrapData<{ message: string; login_url?: string }>(data)
  },
}
