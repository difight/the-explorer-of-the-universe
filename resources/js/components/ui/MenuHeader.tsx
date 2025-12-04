import {
    Stack,
    Button,
    Modal,
    ModalOverlay,
    ModalContent,
    ModalHeader,
    ModalFooter,
    ModalBody,
    ModalCloseButton,
    InputGroup,
    Input,
    InputRightElement,
    InputLeftElement
} from "@chakra-ui/react";
import { useState, useCallback } from "react";
import { useUserStore } from '@/store/userStore';
import { useAlertsStore } from "@/store/alertStore";
import WayToBackend from '@/lib/WayToBackend';
import { router } from '@inertiajs/react';

const MenuHeader = () => {
    // State hooks
    const [showPasswordState, setShowPassword] = useState(false);
    const [showPasswordConfirmState, setShowPasswordConfirm] = useState(false);
    const [isRegisterOpen, setIsRegisterOpen] = useState(false);
    const [isLoginOpen, setIsLoginOpen] = useState(false);
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    });

    // Store hooks
    const addAlert = useAlertsStore((state) => state.addAlert)
    const user = useUserStore((state) => state.user);
    const setUser = useUserStore((state) => state.setUser);
    const clearUser = useUserStore((state) => state.clearUser);

    // Callback hooks
    const showPassword = useCallback(() => setShowPassword(!showPasswordState), [showPasswordState]);
    const showPasswordConfirm = useCallback(() => setShowPasswordConfirm(!showPasswordConfirmState), [showPasswordConfirmState]);
    const handleRegisterOpen = useCallback(() => setIsRegisterOpen(true), []);
    const handleLoginOpen = useCallback(() => setIsLoginOpen(true), []);
    const handleClose = useCallback(() => {
        setIsRegisterOpen(false);
        setIsLoginOpen(false);
        // Сброс формы при закрытии
        setFormData({
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        });
    }, []);
    const handleLogout = async () => {
        try {
            await WayToBackend.logoutUser();
            clearUser();
            // После выхода перенаправляем на главную страницу
            router.visit('/');
        } catch (error) {
            console.error(error)
        }
    }

    // Обработчик изменений полей формы
    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleRegister = async () => {
        // Проверка совпадения паролей
        if (formData.password !== formData.password_confirmation) {
            addAlert({
                message: 'Пароли не совпадают',
            });
            return;
        }

        try {
            const result = await WayToBackend.registerUser({
                name: formData.name,
                email: formData.email,
                password: formData.password,
                password_confirmation: formData.password_confirmation
            });
            setUser(result.data.data)
            handleClose();
        } catch (error) {
            // Ошибка будет обработана в WayToBackend
            console.error(error);
        }
    }

    const handleLogin = async () => {
        try {
            const result = await WayToBackend.loginUser({
                email: formData.email,
                password: formData.password
            });
            if (result.success && result.data.data) {
                setUser(result.data.data);
                handleClose();
            } else {
                addAlert({ message: "Ошибка при входе. Пожалуйста, проверьте email и пароль." });
            }
        } catch (error) {
            // Ошибка будет обработана в WayToBackend
            console.error(error);
        }
    }

    const handleMyAccount = async () => {
        // Проверяем статус авторизации через API
        if (user) {
            router.visit('/my');
        } else {
            // Проверяем наличие токена в localStorage
            const accessToken = localStorage.getItem('access_token');
            if (accessToken) {
                // Если есть токен, перенаправляем на страницу /my
                // ProtectedRoute сам обновит данные пользователя
                router.visit('/my');
            } else {
                addAlert({ message: "Пожалуйста, войдите в систему для доступа к личному кабинету." });
            }
        }
    }

    return (
        <>
            <Stack direction="row" spacing={4} float={"right"} pt={"20px"} pr={"20px"}>
                {!user ? (
                    <>
                        <Button colorScheme='teal' variant='solid' onClick={handleRegisterOpen}>
                            Регистрация
                        </Button>
                        <Button colorScheme='teal' variant='outline' onClick={handleLoginOpen}>
                            Вход
                        </Button>
                    </>
                ) : (
                        <>
                            <Button colorScheme='teal' variant='solid' onClick={handleMyAccount}>
                                Кабинет
                            </Button>
                            <Button colorScheme='teal' variant='solid' onClick={() => router.visit('/profile')}>
                                Профиль
                            </Button>
                            <Button colorScheme='teal' variant='solid'>
                                Мой корабль
                            </Button>
                            <Button colorScheme='teal' variant='outline' onClick={handleLogout}>
                                Выход
                            </Button>
                        </>
                )}
            </Stack>

            {/* Модальное окно регистрации */}
            <Modal isOpen={isRegisterOpen} onClose={handleClose}>
                <ModalOverlay />
                <ModalContent>
                    <ModalHeader>Регистрация</ModalHeader>
                    <ModalCloseButton />
                    <ModalBody>
                        <Stack spacing={3}>
                            <Input
                                variant='outline'
                                placeholder='Имя'
                                name="name"
                                value={formData.name}
                                onChange={handleInputChange}
                            />
                            <InputGroup>
                                <InputLeftElement pointerEvents='none' color='gray.300' fontSize='1.2em'>
                                @
                                </InputLeftElement>
                                <Input
                                    placeholder='Введите почту'
                                    name="email"
                                    type="email"
                                    value={formData.email}
                                    onChange={handleInputChange}
                                />
                            </InputGroup>
                            <InputGroup size='md'>
                                <Input
                                    pr='4.5rem'
                                    type={showPasswordState ? 'text' : 'password'}
                                    placeholder='Пароль'
                                    name="password"
                                    value={formData.password}
                                    onChange={handleInputChange}
                                />
                                <InputRightElement width='4.5rem'>
                                    <Button h='1.75rem' size='sm' onClick={showPassword}>
                                        {showPasswordState ? 'Hide' : 'Show'}
                                    </Button>
                                </InputRightElement>
                            </InputGroup>
                            <InputGroup size='md'>
                                <Input
                                    pr='4.5rem'
                                    type={showPasswordConfirmState ? 'text' : 'password'}
                                    placeholder='Повторите пароль'
                                    name="password_confirmation"
                                    value={formData.password_confirmation}
                                    onChange={handleInputChange}
                                />
                                <InputRightElement width='4.5rem'>
                                    <Button h='1.75rem' size='sm' onClick={showPasswordConfirm}>
                                    {showPasswordConfirmState ? 'Hide' : 'Show'}
                                    </Button>
                                </InputRightElement>
                            </InputGroup>
                        </Stack>
                    </ModalBody>
                    <ModalFooter>
                        <Button onClick={handleRegister} colorScheme='blue' mr={3}>
                            Регистрация
                        </Button>
                        <Button onClick={handleClose}>Закрыть</Button>
                    </ModalFooter>
                </ModalContent>
            </Modal>

            {/* Модальное окно входа */}
            <Modal isOpen={isLoginOpen} onClose={handleClose}>
                <ModalOverlay />
                <ModalContent>
                    <ModalHeader>Вход</ModalHeader>
                    <ModalCloseButton />
                    <ModalBody>
                        <Stack spacing={3}>
                            <InputGroup>
                                <InputLeftElement pointerEvents='none' color='gray.300' fontSize='1.2em'>
                                @
                                </InputLeftElement>
                                <Input
                                    placeholder='Введите почту'
                                    name="email"
                                    type="email"
                                    value={formData.email}
                                    onChange={handleInputChange}
                                />
                            </InputGroup>
                            <InputGroup size='md'>
                                <Input
                                    pr='4.5rem'
                                    type={showPasswordState ? 'text' : 'password'}
                                    placeholder='Пароль'
                                    name="password"
                                    value={formData.password}
                                    onChange={handleInputChange}
                                />
                                <InputRightElement width='4.5rem'>
                                    <Button h='1.75rem' size='sm' onClick={showPassword}>
                                        {showPasswordState ? 'Hide' : 'Show'}
                                    </Button>
                                </InputRightElement>
                            </InputGroup>
                        </Stack>
                    </ModalBody>
                    <ModalFooter>
                        <Button onClick={handleLogin} colorScheme='blue' mr={3}>
                            Вход
                        </Button>
                        <Button onClick={handleClose}>Закрыть</Button>
                    </ModalFooter>
                </ModalContent>
            </Modal>
        </>
    );
};

export default MenuHeader;
