import apiClient from './client';
import { Venue, ApiResponse } from '../types';

export const venuesApi = {
  async getAll(): Promise<Venue[]> {
    const response = await apiClient.get<{ data: Venue[] }>('/venues');
    return response.data.data;
  },

  async getById(id: number): Promise<Venue> {
    const response = await apiClient.get<{ data: Venue }>(`/venues/${id}`);
    return response.data.data;
  },
};

