var elixir = require('laravel-elixir');

/*
 |----------------------------------------------------------------
 | Have a Drink!
 |----------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic
 | Gulp tasks for your Laravel application. Elixir supports
 | several common CSS, JavaScript and even testing tools!
 |
 */

elixir(function(mix) {
    // mix.less(['!./less/includes/**/*'])
    mix.less(['styles.less'])
       .coffee();
});

// elixir(function(mix) {
//     mix.sass("bootstrap.scss")
//        .routes()
//        .events()
//        .phpUnit();
// });