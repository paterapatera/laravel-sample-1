# ドメインモデル図
## ユーザー
```mermaid
flowchart TD
App\UserInterface\Object\User[ユーザー]
string(29) "app|UserInterface|Object|User"
click App\UserInterface\Object\User "https://github.com/paterapatera/laravel-sample-1/tree/main/app|UserInterface|Object|User.php" _blank
App\UserInterface\Object\User --> App\UserInterface\Object\User\Profile
App\UserInterface\Object\User --> App\UserInterface\Object\User\CommentList
App\UserInterface\Object\User\Profile[プロフィール]
string(37) "app|UserInterface|Object|User|Profile"
click App\UserInterface\Object\User\Profile "https://github.com/paterapatera/laravel-sample-1/tree/main/app|UserInterface|Object|User|Profile.php" _blank
App\UserInterface\Object\User\Profile --> App\UserInterface\Object\User\Profile\Name
App\UserInterface\Object\User\Profile\Name[名前]
string(42) "app|UserInterface|Object|User|Profile|Name"
click App\UserInterface\Object\User\Profile\Name "https://github.com/paterapatera/laravel-sample-1/tree/main/app|UserInterface|Object|User|Profile|Name.php" _blank
App\UserInterface\Object\User\Profile\Name --> App\UserInterface\Object\User\Profile\Name\First
App\UserInterface\Object\User\Profile\Name --> App\UserInterface\Object\User\Profile\Name\Last
App\UserInterface\Object\User\Profile\Name\First[名]
string(48) "app|UserInterface|Object|User|Profile|Name|First"
click App\UserInterface\Object\User\Profile\Name\First "https://github.com/paterapatera/laravel-sample-1/tree/main/app|UserInterface|Object|User|Profile|Name|First.php" _blank
App\UserInterface\Object\User\Profile\Name\Last[姓]
string(47) "app|UserInterface|Object|User|Profile|Name|Last"
click App\UserInterface\Object\User\Profile\Name\Last "https://github.com/paterapatera/laravel-sample-1/tree/main/app|UserInterface|Object|User|Profile|Name|Last.php" _blank
App\UserInterface\Object\User\CommentList[コメントリスト]
string(41) "app|UserInterface|Object|User|CommentList"
click App\UserInterface\Object\User\CommentList "https://github.com/paterapatera/laravel-sample-1/tree/main/app|UserInterface|Object|User|CommentList.php" _blank
```
## ユーザー
```mermaid
flowchart TD
App\Console\Commands\User[ユーザー]
string(25) "app|Console|Commands|User"
click App\Console\Commands\User "https://github.com/paterapatera/laravel-sample-1/tree/main/app|Console|Commands|User.php" _blank
App\Console\Commands\User --> App\Console\Commands\Contact
App\Console\Commands\Contact[連絡先]
string(28) "app|Console|Commands|Contact"
click App\Console\Commands\Contact "https://github.com/paterapatera/laravel-sample-1/tree/main/app|Console|Commands|Contact.php" _blank
App\Console\Commands\Contact --> App\Console\Commands\Tel
App\Console\Commands\Contact --> App\Console\Commands\Email
App\Console\Commands\Tel[電話番号]
string(24) "app|Console|Commands|Tel"
click App\Console\Commands\Tel "https://github.com/paterapatera/laravel-sample-1/tree/main/app|Console|Commands|Tel.php" _blank
App\Console\Commands\Email[メールアドレス]
string(26) "app|Console|Commands|Email"
click App\Console\Commands\Email "https://github.com/paterapatera/laravel-sample-1/tree/main/app|Console|Commands|Email.php" _blank
```
