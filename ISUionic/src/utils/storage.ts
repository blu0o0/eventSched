import { Preferences } from '@capacitor/preferences';

const TOKEN_KEY = 'auth_token';
const USER_KEY = 'user_data';
const REMEMBER_ME_KEY = 'remember_me';

export const storage = {
  async setToken(token: string): Promise<void> {
    await Preferences.set({ key: TOKEN_KEY, value: token });
  },

  async getToken(): Promise<string | null> {
    const { value } = await Preferences.get({ key: TOKEN_KEY });
    return value;
  },

  async removeToken(): Promise<void> {
    await Preferences.remove({ key: TOKEN_KEY });
  },

  async setUser(user: any): Promise<void> {
    await Preferences.set({ key: USER_KEY, value: JSON.stringify(user) });
  },

  async getUser(): Promise<any | null> {
    try {
      const { value } = await Preferences.get({ key: USER_KEY });
      if (!value || value === 'undefined' || value === 'null') {
        return null;
      }
      return JSON.parse(value);
    } catch (error) {
      console.error('Error parsing user data from storage:', error);
      // Clear invalid data
      await storage.removeUser();
      return null;
    }
  },

  async removeUser(): Promise<void> {
    await Preferences.remove({ key: USER_KEY });
  },

  async setRememberMe(value: boolean): Promise<void> {
    await Preferences.set({ key: REMEMBER_ME_KEY, value: value.toString() });
  },

  async getRememberMe(): Promise<boolean> {
    const { value } = await Preferences.get({ key: REMEMBER_ME_KEY });
    return value === 'true';
  },

  async clear(): Promise<void> {
    await Preferences.clear();
  }
};

