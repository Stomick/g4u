<?php
/**
 * Created by PhpStorm.
 * User: agsto
 * Date: 01.08.2018
 * Time: 12:44
 */

namespace app\components;


class ErrorType
{
    const user = [
        'ru' => 'Пользователь',
        'eu' => 'User',
        'uk' => 'Пользователь',

    ];
    const user_error = [
        'ru' => 'Пользователь не найден',
        'eu' => 'User not found',
        'uk' => 'Пользователя не знайденно',

    ];

    const passwd = [
        'ru' => 'Пароль',
        'eu' => 'Password',
        'uk' => 'Пароль',

    ];

    const err_passwd = [
        'ru' => 'Не правильный пароль',
        'eu' => 'Wrong password',
        'uk' => 'Невірний пароль',

    ];

    const login = [
        'ru' => 'Логин',
        'eu' => 'Login',
        'uk' => 'Логiн',

    ];

    const err_login = [
        'ru' => 'Не правильный логин',
        'eu' => 'Wrong password',
        'uk' => 'Невірний логiн',

    ];
    const email = [
        'ru' => 'Почта',
        'eu' => 'Emeil',
        'uk' => 'Пошта',

    ];

    const err_emeil = [
        'ru' => 'Не правильная почта',
        'eu' => 'Wrong email',
        'uk' => 'Невірна пошта',

    ];

    const player_not_found = [
        'ru' => 'Игрок не найден',
        'eu' => 'Player not found',
        'uk' => 'Гравеця не знайдено',
    ];

    const player_not_merget = [
        'ru' => 'Ошибка при объединении',
        'eu' => 'Player not merged',
        'uk' => 'Помилка при об`єднанні',

    ];

    const not_found = [
        'ru' => 'Не найдено',
        'eu' => 'Not found',
        'uk' => 'Не знайдено',
    ];

    const not_update = [
        'ru' => 'Ошибка обновления',
        'eu' => 'Update fail',
        'uk' => 'Оновлення невдало',
    ];

    const country_stus_on = [
        'ru' => 'Включенно',
        'eu' => 'On',
        'uk' => 'Включено',
    ];

    const country_stus_off = [
        'ru' => 'Выключено',
        'eu' => 'Off',
        'uk' => 'Вимкнено',
    ];

    const not_save = [
        'ru' => 'Сохранить не удалось',
        'eu' => 'Fail saved',
        'uk' => 'Не вдалося зберегти',
    ];

    const not_add = [
        'ru' => 'Добавить не удалось',
        'eu' => 'Added false',
        'uk' => 'Не вдалося додаты',
    ];

    const answer_true_update = [
        'ru' => 'Обновлено успешно',
        'eu' => 'Successfully',
        'uk' => 'Оновлено успішно',
    ];

    const answer_true_save = [
        'ru' => 'Сохранено успешно',
        'eu' => 'Successfully',
        'uk' => 'Сохранено успішно',
    ];

    const answer_true_add = [
        'ru' => 'Добавлено успешно',
        'eu' => 'Successfully',
        'uk' => 'Додано успішно',
    ];

    const answer_true_add_game_to_tournament = [
        'ru' => 'Добавлено успешно',
        'eu' => 'Successfully',
        'uk' => 'Додано успішно',
    ];

    const answer_false_add_game_to_tournament = [
        'ru' => 'Не возможно добавить',
        'eu' => 'Added false',
        'uk' => 'Не вдалося додаты',
    ];

    const answer_true_delete = [
        'ru' => 'Удалено успешно',
        'eu' => 'Deleted',
        'uk' => 'Убрано успішно',
    ];

    const answer_false_delete = [
        'ru' => 'Не удалено',
        'eu' => 'Not deleted',
        'uk' => 'Убрано не успішно',
    ];

    const answer_false_autocomplite = [
        'ru' => 'Нужно добавить команды',
        'eu' => 'Need added command',
        'uk' => 'Треба додаты команди',
    ];

    const personal = [
        'name' => [
            'ru' => 'Имя',
            'eu' => 'Name',
            'uk' => 'И`мя',
        ],
        'surename' => [
            'ru' => 'Фамилия',
            'eu' => 'Surename',
            'uk' => 'Прізвище',
        ],
        'photo' => [
            'ru' => 'Фотография',
            'eu' => 'Photo',
            'uk' => 'Свитлина',
        ],
        'type_id' => [
            'ru' => 'Должность',
            'eu' => 'Position',
            'uk' => 'Посада',
        ],
        'error' => [
            'ru' => 'Пустое поле',
            'eu' => 'Empty field',
            'uk' => 'Пусте поле',
        ]
    ];
    const command_error = [
        'title' => [
            'ru' => 'Название',
            'eu' => 'Title',
            'uk' => 'Назва',
        ],

        'city_id' => [
            'ru' => 'Город',
            'eu' => 'City',
            'uk' => 'Miсто',
        ],
        'error' => [
            'ru' => 'Пустое поле',
            'eu' => 'Empty field',
            'uk' => 'Пусте поле',
        ]
    ];
    const leagues = [
        'name' => [
            'ru' => 'Название',
            'eu' => 'Title',
            'uk' => 'Назва',
        ],
        'description' => [
            'ru' => 'Описание',
            'eu' => 'Description',
            'uk' => 'Описання',
        ],
        'photo' => [
            'ru' => 'Лого лиги',
            'eu' => 'Logo league',
            'uk' => 'Лого лiги',
        ],
        'franchise_id' => [
            'ru' => 'Франшиза',
            'eu' => 'Franchise',
            'uk' => 'Франчаiсе',
        ],
        'country_id' => [
            'ru' => 'Страна',
            'eu' => 'Country',
            'uk' => 'Страна',
        ],
        'error' => [
            'ru' => 'Не выбрано',
            'eu' => 'Not select',
            'uk' => 'Не вибрано',
        ]
    ];
    const sub_leagues = [
        'title' => [
            'ru' => 'Название',
            'eu' => 'Title',
            'uk' => 'Назва',
        ],
        'league_id' => [
            'ru' => 'Лига',
            'eu' => 'League',
            'uk' => 'Лiга',
        ],
        'error' => [
            'ru' => 'Не выбрано',
            'eu' => 'Not select',
            'uk' => 'Не вибрано',
        ]
    ];
    const merge = [
        'true' => [
            'ru' => 'Запрос отправлен',
            'eu' => 'Request has been sent',
            'uk' => 'Запит відправлено '
        ],
        'false' => [
            'ru' => 'Запрос некорректен',
            'eu' => 'Request is incorrect',
            'uk' => 'Запит некоректний'
        ]
    ];

    const admin = [
        'not_permission' => [
            'ru' => 'Нехватает прав доступа',
            'eu' => 'You not have permission',
            'uk' => 'Ви не маєте дозволу',
        ],
        'not_permission_leag' => [
            'ru' => 'Нехватает прав доступа к этой лиге',
            'eu' => 'You not have permission for this league',
            'uk' => 'Ви не маєте дозволу до цiеi лiгi',
        ]
    ];
}