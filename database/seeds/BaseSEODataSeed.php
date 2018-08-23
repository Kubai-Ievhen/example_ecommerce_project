<?php

use Illuminate\Database\Seeder;

class BaseSEODataSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seo = <<<SEO
<meta name=\"viewport\" content=\"width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no\">
<meta name=\"author\" content=\"Revival Pixel\">
<meta property=\"og:title\" content=\"Anant\">
<meta property=\"og:description\" content=\"Multi Purpose HTML Template\">
<meta property=\"og:site_name\" content=\"Anant\">
<meta property=\"og:url\" content=\"http://anant.revivalpixel.com/html/index.html\">
<meta property=\"og:image\" content=\"http://anant.revivalpixel.com/html/images/anant-logo.png\">
<meta name=\"twitter:card\" content=\"Multi Purpose HTML Template\">
<meta name=\"twitter:site\" content=\"http://anant.revivalpixel.com/html/index.html\">
<meta name=\"twitter:creator\" content=\"Revival Pixel\">
<meta name=\"twitter:title\" content=\"Anant\">
<meta name=\"twitter:description\" content=\"http://anant.revivalpixel.com/html/index.html\">
<meta name=\"twitter:image\" content=\"http://anant.revivalpixel.com/html/images/anant-logo.png\">

<!-- Favicon Icon -->
<link rel=\"shortcut icon\" href=\"images/favicon.png\">

<!--Icons-->

<link rel=\"apple-touch-icon\" sizes=\"57x57\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/apple-touch-icon-57x57.png\">

<link rel=\"apple-touch-icon\" sizes=\"60x60\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/apple-touch-icon-60x60.png\">

<link rel=\"apple-touch-icon\" sizes=\"72x72\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/apple-touch-icon-72x72.png\">

<link rel=\"apple-touch-icon\" sizes=\"76x76\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/apple-touch-icon-76x76.png\">

<link rel=\"apple-touch-icon\" sizes=\"114x114\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/apple-touch-icon-114x114.png\">

<link rel=\"apple-touch-icon\" sizes=\"120x120\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/apple-touch-icon-120x120.png\">

<link rel=\"apple-touch-icon\" sizes=\"144x144\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/apple-touch-icon-144x144.png\">

<link rel=\"apple-touch-icon\" sizes=\"152x152\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/apple-touch-icon-152x152.png\">

<link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/apple-touch-icon-180x180.png\">

<link rel=\"icon\" type=\"image/png\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/favicon-32x32.png\" sizes=\"32x32\">

<link rel=\"icon\" type=\"image/png\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/favicon-194x194.png\" sizes=\"194x194\">

<link rel=\"icon\" type=\"image/png\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/favicon-96x96.png\" sizes=\"96x96\">

<link rel=\"icon\" type=\"image/png\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/android-chrome-192x192.png\" sizes=\"192x192\">

<link rel=\"icon\" type=\"image/png\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/favicon-16x16.png\" sizes=\"16x16\">

<link rel=\"manifest\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/manifest.json\">

<link rel=\"shortcut icon\" href=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/favicon.ico\">

<meta name=\"msapplication-TileColor\" content=\"#ffffff\">

<meta name=\"msapplication-TileImage\" content=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/mstile-144x144.png\">

<meta name=\"msapplication-config\" content=\"http://www.arifleet.com/wp-content/themes/ari_theme/favicons/browserconfig.xml\">

<meta name=\"theme-color\" content=\"#ffffff\">
SEO;
        DB::table('base_s_e_o_datas')->insert(['value' => $seo, 'name' => 'Base', 'note'=>'Base Content']);

    }
}
