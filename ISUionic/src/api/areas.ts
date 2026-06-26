import apiClient from './client';
import { Area } from '../types';

export const areasApi = {
  async getAll(): Promise<Area[]> {
    const response = await apiClient.get<Area[]>('/areas');
    return response.data;
  },

  async getById(id: number): Promise<Area> {
    const response = await apiClient.get<Area>(`/areas/${id}`);
    return response.data;
  },
};
