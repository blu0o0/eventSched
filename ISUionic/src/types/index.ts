// Type definitions for the application

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'administrator' | 'osas' | 'main_proponent' | 'general_user';
  created_at?: string;
  updated_at?: string;
}

export interface Venue {
  id: number;
  name: string;
  location: string;
  capacity: number;
  description: string | null;
  map_coordinates: string | null; // Format: "latitude,longitude"
  photo_url?: string | null;
  status: 'available' | 'damaged' | 'under_construction';
  unavailable_until?: string | null; // YYYY-MM-DD
  days_until_available?: number | null;
  is_available: boolean;
  is_unavailable: boolean;
  created_at: string;
  updated_at: string;
}

export interface Reservation {
  id: number;
  title: string;
  description: string | null;
  venue: Venue;
  venue_id: number;
  date: string; // YYYY-MM-DD
  start_time: string; // HH:mm
  end_time: string; // HH:mm
  capacity: number;
  status: 'pending' | 'approved' | 'rejected' | 'postponed';
  user: { id: number; name: string; email: string };
  user_id: number;
  approved_by: number | null;
  approved_at: string | null;
  rejection_reason: string | null;
  postponement_reason?: string | null;
  is_postponed?: boolean;
  edited_at: string | null;
  is_edited: boolean;
  created_at: string;
  updated_at: string;
}

export interface EmergencyReport {
  id: number;
  type: string;
  description: string;
  reporter: { id: number; name: string; email: string };
  reporter_id: number;
  status: 'open' | 'closed';
  resolved_by: number | null;
  resolved_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface CalendarEvent {
  id: number;
  title: string;
  start: string; // ISO 8601 format
  end: string; // ISO 8601 format
  color: string;
  backgroundColor: string;
  borderColor: string;
  extendedProps: {
    venue: string;
    status: string;
    description: string;
    capacity: number;
    user: string;
  };
}

export interface LoginCredentials {
  email: string;
  password: string;
  remember_me?: boolean;
  recaptcha_token?: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role?: 'main_proponent' | 'general_user';
}

export interface OtpSendResponse {
  message: string;
}

export interface OtpVerifyResponse {
  message: string;
  email_verified?: boolean;
}

export interface ForgotPasswordData {
  email: string;
  otp: string;
  password: string;
  password_confirmation: string;
}

export interface CreateReservationData {
  title: string;
  description?: string;
  venue_id: number;
  date: string; // YYYY-MM-DD
  start_time: string; // HH:mm
  end_time: string; // HH:mm
  capacity: number;
}

export interface CreateEmergencyData {
  type: string;
  description: string;
}

export interface RescheduleReservationData {
  venue_id?: number;
  date?: string; // YYYY-MM-DD
  start_time?: string; // HH:mm
  end_time?: string; // HH:mm
}

export interface ApiResponse<T> {
  data?: T;
  message?: string;
  meta?: any;
}