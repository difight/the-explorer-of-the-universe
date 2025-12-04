import { router } from '@inertiajs/react';

// Функция для навигации по маршрутам
export const navigate = (path: string, options = {}) => {
    router.visit(path, options);
};

// Объект с маршрутами приложения
export const routes = {
    home: '/',
    my: '/my',
    profile: '/profile',
};

export default router;
