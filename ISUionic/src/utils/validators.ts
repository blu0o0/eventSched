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

  hasLetter: (password: string): boolean => {
    return /[A-Za-z]/.test(password);
  },

  hasNumber: (password: string): boolean => {
    return /\d/.test(password);
  },

  hasSymbol: (password: string): boolean => {
    return /[@$!%*?&.,]/.test(password);
  },

  passwordStrong: (password: string): boolean => {
    // At least 8 characters, must contain letter, number, and symbol
    return validators.hasLetter(password) && 
           validators.hasNumber(password) && 
           validators.hasSymbol(password) && 
           validators.password(password);
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

  dateNotTooSoon: (date: string): boolean => {
    const selectedDate = new Date(date);
    const minDate = new Date();
    minDate.setDate(minDate.getDate() + 6);
    minDate.setHours(0, 0, 0, 0);
    return selectedDate >= minDate;
  },

  positiveNumber: (value: number): boolean => {
    return value > 0;
  },
};

/**
 * Format military time (HH:mm) to AM/PM format (h:mm AM/PM)
 */
export function formatTime(timeString: string): string {
  if (!timeString) return '';
  const [hours, minutes] = timeString.split(':');
  const h = parseInt(hours, 10);
  const ampm = h >= 12 ? 'PM' : 'AM';
  const hour12 = h % 12 || 12;
  return `${hour12}:${minutes} ${ampm}`;
}

export interface ValidationRule {
  validator: (value: any, ...args: any[]) => boolean;
  message: string;
  args?: any[];
}

export function validateField(value: any, rules: ValidationRule[]): string | null {
  for (const rule of rules) {
    const args = rule.args ? [value, ...rule.args] as [any, ...any[]] : [value] as [any];
    if (!rule.validator.apply(null, args)) {
      return rule.message;
    }
  }
  return null;
}

