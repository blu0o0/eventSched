import apiClient from './client';
import { LoginCredentials, RegisterData, User, ApiResponse } from '../types';

export const authApi = {
  async login(credentials: LoginCredentials): Promise<{ user: User; token: string }> {
    const response = await apiClient.post<{ user: User; token: string }>('/login', credentials);
    return response.data;
  },

  async register(data: RegisterData): Promise<{ user: User; token: string }> {
    const response = await apiClient.post<{ user: User; token: string }>('/register', data);
    return response.data;
  },

  async logout(): Promise<void> {
    await apiClient.post('/logout');
  },

  async getCurrentUser(): Promise<User> {
    const response = await apiClient.get<User>('/user');
    return response.data;
  },
};

