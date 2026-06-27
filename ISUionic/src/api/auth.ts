import apiClient from './client';
import { LoginCredentials, RegisterData, User, OtpSendResponse, OtpVerifyResponse, ForgotPasswordData } from '../types';

export const authApi = {
  async login(credentials: LoginCredentials): Promise<{ user: User; token: string }> {
    const response = await apiClient.post<{ user: User; token: string }>('/login', credentials);
    return response.data;
  },

  async register(data: RegisterData & { email_verified: boolean }): Promise<{ user: User; token: string }> {
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

  // OTP endpoints
  async sendVerificationOtp(email: string): Promise<OtpSendResponse> {
    const response = await apiClient.post<OtpSendResponse>('/auth/send-verification-otp', { email });
    return response.data;
  },

  async verifyEmailOtp(email: string, otp: string): Promise<OtpVerifyResponse> {
    const response = await apiClient.post<OtpVerifyResponse>('/auth/verify-email-otp', { email, otp });
    return response.data;
  },

  async sendResetOtp(email: string): Promise<OtpSendResponse> {
    const response = await apiClient.post<OtpSendResponse>('/forgot-password/send-otp', { email });
    return response.data;
  },

  async verifyResetOtpOnly(data: { email: string; otp: string }): Promise<{ message: string; otp_valid?: boolean }> {
    const response = await apiClient.post<{ message: string; otp_valid?: boolean }>('/forgot-password/verify-otp', data);
    return response.data;
  },

  async resetPassword(data: ForgotPasswordData): Promise<{ message: string }> {
    const response = await apiClient.post<{ message: string }>('/forgot-password/reset-password', data);
    return response.data;
  },

  async updateName(name: string): Promise<{ message: string; user: User }> {
    const response = await apiClient.put<{ message: string; user: User }>('/profile/name', { name });
    return response.data;
  },

  async updateRole(role: string): Promise<{ message: string; user: User }> {
    const response = await apiClient.put<{ message: string; user: User }>('/profile/role', { role });
    return response.data;
  },
};
