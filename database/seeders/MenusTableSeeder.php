<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Menu::create([
            'id' => 1,
            'flg_admin' => 1,
            'title' => '管理者',
            'icon' => 'fas fa-fw fa-user',
            'sort_number' => 100,
        ]);
        Menu::create([
            'id' => 2,
            'flg_admin' => 1,
            'title' => '一覧',
            'link' => '/user',
            'parent_id' => 1,
            'sort_number' => 200,
        ]);
        Menu::create([
            'id' => 3,
            'flg_admin' => 1,
            'title' => '作成',
            'link' => '/user/create',
            'parent_id' => 1,
            'sort_number' => 300,
        ]);

        Menu::create([
            'id' => 4,
            'flg_admin' => 1,
            'title' => 'テナント',
            'icon' => 'fas fa-fw fa-share',
            'sort_number' => 400,
        ]);
        Menu::create([
            'id' => 5,
            'flg_admin' => 1,
            'title' => '一覧',
            'link' => '/tenant',
            'parent_id' => 4,
            'sort_number' => 500,
        ]);
        Menu::create([
            'id' => 6,
            'flg_admin' => 1,
            'title' => '作成',
            'link' => '/tenant/create',
            'parent_id' => 4,
            'sort_number' => 600,
        ]);

        Menu::create([
            'id' => 7,
            'flg_admin' => 0,
            'title' => 'ドメイン',
            'icon' => 'fas fa-fw fa-store',
            'sort_number' => 700,
        ]);
        Menu::create([
            'id' => 8,
            'flg_admin' => 0,
            'title' => '一覧',
            'link' => '/domain',
            'parent_id' => 7,
            'sort_number' => 800,
        ]);
        Menu::create([
            'id' => 9,
            'flg_admin' => 0,
            'title' => '作成',
            'link' => '/domain/create',
            'parent_id' => 7,
            'sort_number' => 900,
        ]);

        Menu::create([
            'id' => 10,
            'flg_admin' => 0,
            'title' => '設定パラメータ',
            'icon' => 'fas fa-fw fa-file',
            'sort_number' => 1000,
        ]);
        Menu::create([
            'id' => 11,
            'flg_admin' => 0,
            'title' => '一覧',
            'link' => '/config',
            'parent_id' => 10,
            'sort_number' => 1100,
        ]);
    }
}
