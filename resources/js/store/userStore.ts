import { create } from 'zustand';
import { UserData, UserStore } from '@/types';

export const useUserStore = create<UserStore>((set) => ({
  user: null,
  setUser: (user: UserData | null) => set({ user }),
  clearUser: () => set({ user: null }),
}));

const userStore = useUserStore;
export default userStore;
