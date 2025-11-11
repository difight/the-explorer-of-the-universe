import { create } from 'zustand';
import { User, UserStore } from '@/types';

export const useUserStore = create<UserStore>((set) => ({
  user: null,
  setUser: (user: User | null) => set({ user }),
  clearUser: () => set({ user: null }),
}));

// Для обратной совместимости, если вы импортируете как default
const userStore = useUserStore;
export default userStore;