export interface Project {
  id: string;
  slug: string;
  title: string;
  category: string;
  location: string;
  year: number;
  coverImage: string;
  images: string[];
  description: string;
  longDescription?: string;
  hasVirtualTour: boolean;
  tourSlug?: string;
  beforeImage?: string;
  afterImage?: string;
  area?: string;
  status?: string;
  isFeatured?: boolean;
}

export interface TourHotspot {
  id: string;
  yaw: number;
  pitch: number;
  label: string;
  targetSceneId?: string;
}

export interface TourScene {
  id: string;
  name: string;
  panoramaUrl: string;
  thumbnailUrl: string;
  yaw?: number;
  pitch?: number;
  hotspots?: TourHotspot[];
}

export interface VirtualTour {
  id: string;
  slug: string;
  title: string;
  description: string;
  coverImage: string;
  projectSlug?: string;
  scenes: TourScene[];
}

export interface ContactPayload {
  name: string;
  email: string;
  phone?: string;
  service?: string;
  message: string;
}

export interface Testimonial {
  id: string;
  name: string;
  role: string;
  quote: string;
  avatar?: string;
}

export interface ServiceItem {
  id: string;
  title: string;
  description: string;
  image: string;
  icon?: string;
}

export interface ProcessStep {
  number: string;
  title: string;
  description: string;
}
