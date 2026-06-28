import apiClient from './client';
import { Reservation, CreateReservationData, RescheduleReservationData, ConflictingReservation } from '../types';

export interface CreateReservationResponse {
  message: string;
  data?: Reservation;
  conflicts?: ConflictingReservation[];
  error?: string;
}

export const reservationsApi = {
  async getAll(status?: 'pending' | 'approved' | 'rejected' | 'postponed', mine?: boolean): Promise<{ data: Reservation[]; meta?: any }> {
    const params: any = {};
    if (status) params.status = status;
    if (mine) params.mine = 'true';
    const response = await apiClient.get<{ data: Reservation[]; meta?: any }>('/reservations', { params });
    return response.data;
  },

  async getById(id: number): Promise<Reservation> {
    const response = await apiClient.get<{ data: Reservation }>(`/reservations/${id}`);
    return response.data.data;
  },

  async create(data: CreateReservationData | FormData, force: boolean = false): Promise<CreateReservationResponse> {
    const response = await apiClient.post<CreateReservationResponse>('/reservations', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  async update(id: number, data: CreateReservationData | FormData): Promise<Reservation> {
    const response = await apiClient.put<{ message: string; data: Reservation }>(`/reservations/${id}`, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data.data;
  },

  async delete(id: number): Promise<void> {
    await apiClient.delete<{ message: string }>(`/reservations/${id}`);
  },

  async reschedule(id: number, data: RescheduleReservationData): Promise<Reservation> {
    const response = await apiClient.post<{ message: string; data: Reservation }>(`/reservations/${id}/reschedule`, data);
    return response.data.data;
  },
};

