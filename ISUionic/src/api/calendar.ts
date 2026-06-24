import apiClient from './client';
import { CalendarEvent } from '../types';

export const calendarApi = {
  async getEvents(start?: string, end?: string): Promise<CalendarEvent[]> {
    const params: { start?: string; end?: string } = {};
    if (start) params.start = start;
    if (end) params.end = end;
    
    const response = await apiClient.get<CalendarEvent[]>('/calendar/events', { params });
    return response.data;
  },
};

