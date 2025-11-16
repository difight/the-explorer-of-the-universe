// MenuHeader.tsx
import { AlertStore } from '@/types';
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
import { CheckIcon } from "@chakra-ui/icons";
import { useAlertsStore } from "@/store/alertStore";

const MenuHeader = () => {
    // State hooks
    const [showPasswordState, setShowPassword] = useState(false);
    const [showPasswordConfirmState, setShowPasswordConfirm] = useState(false);
    const [isOpen, setIsOpen] = useState(false);

    // Callback hooks
    const showPassword = useCallback(() => setShowPassword(!showPasswordState), [showPasswordState]);
    const showPasswordConfirm = useCallback(() => setShowPasswordConfirm(!showPasswordConfirmState), [showPasswordConfirmState]);
    const handleOpen = useCallback(() => setIsOpen(true), []);
    const handleClose = useCallback(() => setIsOpen(false), []);

    // Store hooks
    const addAlert = useAlertsStore((state) => state.addAlert)
    const user = useUserStore((state) => state.user);

    const handleRegister = () => {
        addAlert({message: 'Ошибка'})
        console.log('Ошибка')
    }

    return (
        <>
            <Stack direction="row" spacing={4} float={"right"} pt={"20px"} pr={"20px"}>
                {!user ? (
                    <Button colorScheme='teal' variant='solid' onClick={handleOpen}>
                        Регистрация
                    </Button>
                ) : (
                    <Button colorScheme='teal' variant='solid'>
                        Кабинет
                    </Button>
                )}
            </Stack>

            <Modal isOpen={isOpen} onClose={handleClose}>
                <ModalOverlay />
                <ModalContent>
                    <ModalHeader>Регистрация</ModalHeader>
                    <ModalCloseButton />
                    <ModalBody>
                        <Stack spacing={3}>
                            <Input variant='outline' placeholder='Имя' name="name" />
                            <InputGroup>
                                <InputLeftElement pointerEvents='none' color='gray.300' fontSize='1.2em'>
                                @
                                </InputLeftElement>
                                <Input placeholder='Введите почту' name="email" type="email" />
                            </InputGroup>
                            <InputGroup size='md'>
                                <Input
                                    pr='4.5rem'
                                    type={showPasswordState ? 'text' : 'password'}
                                    placeholder='Пароль'
                                    name="password"
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
                                    name="password-confirmated"
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
        </>
    );
};

export default MenuHeader;
