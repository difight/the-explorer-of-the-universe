import { create } from 'zustand';
import { UserData, UserStore } from '@/types';
import { persist } from 'zustand/middleware'

// Функция для получения данных пользователя из localStorage
const getUserFromLocalStorage = (): UserData | null => {
    const accessToken = localStorage.getItem('access_token');
    const refreshToken = localStorage.getItem('refresh_token');
    const accessTokenExpiresAt = localStorage.getItem('access_token_expires_at');
    const refreshTokenExpiresAt = localStorage.getItem('refresh_token_expires_at');

    // Если есть токены, пытаемся получить данные пользователя
    if (accessToken && refreshToken && accessTokenExpiresAt && refreshTokenExpiresAt) {
        // Здесь мы не можем получить данные пользователя без API запроса,
        // поэтому возвращаем объект с токенами
        return {
            access_token: accessToken,
            refresh_token: refreshToken,
            access_token_expires_at: accessTokenExpiresAt,
            refresh_token_expires_at: refreshTokenExpiresAt,
            user: {
                id: 0,
                name: '',
                email: '',
            }
        };
    }

    return null;
};

export const useUserStore = create<UserStore>()(
    persist((set) => ({
        user: getUserFromLocalStorage(),
        setUser: (user: UserData | null) => set({ user }),
        clearUser: () => {
            // Очищаем localStorage при выходе
            localStorage.removeItem('access_token');
            localStorage.removeItem('refresh_token');
            localStorage.removeItem('access_token_expires_at');
            localStorage.removeItem('refresh_token_expires_at');
            set({ user: null });
        },
    }),
    { name: 'user-storage' }
    )
);

const userStore = useUserStore;
export default userStore;
