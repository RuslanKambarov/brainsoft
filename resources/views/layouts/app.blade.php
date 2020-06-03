<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ЦТК</title>

    <!-- Scripts -->
    <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
    <script>
    var OneSignal = window.OneSignal || [];
    OneSignal.push(function() {
        OneSignal.init({
        appId: "9f70cb55-3cb9-41c5-97f3-56a41cfb8fe4",
        notifyButton: {
            enable: true,
        },
        });
        OneSignal.sendTag("user_id", {{Auth::id()}}, function(tagsSent) {
            console.log("user added {{Auth::id()}}")
        });
    });
    </script>
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">

    <v-app>
    <v-navigation-drawer v-model="drawer" app>
        <v-list dense>
            <v-list-item link href="/">
                <v-list-item-action>
                    <v-icon>mdi-home</v-icon>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title>Главная</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <v-list-item link href="/users">
                <v-list-item-action>
                    <v-icon>mdi-account</v-icon>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title>Пользователи</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <v-list-item link href="/tasks">
                <v-list-item-action>
                    <v-icon>mdi-calendar-check</v-icon>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title>Задачи</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <v-list-item link href="/audit">
                <v-list-item-action>
                    <v-icon>mdi-eye-check</v-icon>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title>Аудит</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <v-list-group>
                <template v-slot:activator>
                    <v-list-item-action>
                        <v-icon>mdi-eye-check</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Аналитика</v-list-item-title>
                    </v-list-item-content>            
                </template>
                <v-list-item class="ml-10" link href="/analytics/monitor">
                    <v-list-item-content>
                        <v-list-item-title>Аналитика мониторинга</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item class="ml-10" link href="/analytics/audit">
                    <v-list-item-content>
                        <v-list-item-title>Аналитика аудитов</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item class="ml-10" link href="/analytics/coal">
                    <v-list-item-content>
                        <v-list-item-title>Аналитика учета топлива</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>            
            </v-list-group>                        
            <v-list-item link href="/alarms">
                <v-list-item-action>
                    <v-icon>mdi-alert</v-icon>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title>Аварии</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            {{-- <v-list-item link href="/events">
                <v-list-item-action>
                    <v-icon>mdi-contact-mail</v-icon>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title>События</v-list-item-title>
                </v-list-item-content>
            </v-list-item> --}}
            <v-list-item link href="/settings">
                <v-list-item-action>
                    <v-icon>mdi-settings</v-icon>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title>Конфигурация</v-list-item-title>
                </v-list-item-content>
            </v-list-item>            
        </v-list>        
    </v-navigation-drawer>

    <v-app-bar app>
        <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
        <v-toolbar-title>ЦТК</v-toolbar-title>
        <v-spacer></v-spacer>
        @auth
        <a href="/profile"><button class="btn btn-success mr-4">{{Auth::user()->name}}</button></a>
        
        <a href="{{route('logout')}}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><button class="btn btn-danger">Выход</button></a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>    
        @endauth
    </v-app-bar>

    <!-- Sizes your content based upon application components -->
    <v-content>

        <!-- Provides the application the proper gutter -->
        @yield('content')
        <!-- If using vue-router -->
    </v-content>

    <v-footer app>
        <!-- -->
    </v-footer>
    </v-app>
    </div>
</body>
</html>
