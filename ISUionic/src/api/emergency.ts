import apiClient from './client';
import { EmergencyReport, CreateEmergencyData, ApiResponse } from '../types';

export const emergencyApi = {
  async create(data: CreateEmergencyData): Promise<EmergencyReport> {
    const response = await apiClient.post<{ message: string; data: EmergencyReport }>('/emergency', data);
    return response.data.data;
  },

  async getAll(): Promise<{ data: EmergencyReport[]; meta?: any }> {
    const response = await apiClient.get<{ data: EmergencyReport[]; meta?: any }>('/emergency/list');
    return response.data;
  },

  async getById(id: number): Promise<EmergencyReport> {
    const response = await apiClient.get<{ data: EmergencyReport }>(`/emergency/${id}`);
    return response.data.data;
  },
};

