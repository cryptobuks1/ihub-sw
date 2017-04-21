<?php

namespace BetGames;

use iHubGrid\Accounting\Users\IntegrationUser;

class TestUser
{
    public function getUser()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests');
    }

    public function getBalanceInCents()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests')->getBalanceInCents();
    }

    public function getCurrency()
    {
        return IntegrationUser::get(env('TEST_USER_ID'), 0, 'tests')->getCurrency();
    }
}