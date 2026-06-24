// Form validation utilities

export const validators = {
  email: (email: string): boolean => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  },

  required: (value: any): boolean => {
    if (typeof value === 'string') {
      return value.trim().length > 0;
    }
    return value !== null && value !== undefined;
  },

  minLength: (value: string, min: number): boolean => {
    return value.length >= min;
  },

  password: (password: string): boolean => {
    // At least 8 characters
    return password.length >= 8;
  },

  passwordMatch: (password: string, confirmPassword: string): boolean => {
    return password === confirmPassword;
  },

  timeAfter: (startTime: string, endTime: string): boolean => {
    if (!startTime || !endTime) return false;
    if (!startTime.includes(':') || !endTime.includes(':')) return false;
    const [startHour, startMin] = startTime.split(':').map(Number);
    const [endHour, endMin] = endTime.split(':').map(Number);
    const startMinutes = startHour * 60 + startMin;
    const endMinutes = endHour * 60 + endMin;
    return endMinutes > startMinutes;
  },

  dateNotPast: (date: string): boolean => {
    const selectedDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return selectedDate >= today;
  },

  positiveNumber: (value: number): boolean => {
    return value > 0;
  },
};

export interface ValidationRule {
  validator: (value: any, ...args: any[]) => boolean;
  message: string;
  args?: any[];
}

export function validateField(value: any, rules: ValidationRule[]): string | null {
  for (const rule of rules) {
    const args = rule.args ? [value, ...rule.args] : [value];
    if (!rule.validator(...args)) {
      return rule.message;
    }
  }
  return null;
}

