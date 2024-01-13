<?php
    require_once dirname(__DIR__, $levels = 2) .'/api/util.php';
    // Helpers here serve as example. Change to suit your needs.

    const VITE_HOST = 'http://127.0.0.1:5173';

    // For a real-world example check here:
    // https://github.com/wp-bond/bond/blob/master/src/Tooling/Vite.php
    // https://github.com/wp-bond/boilerplate/tree/master/app/themes/boilerplate

    // you might check @vitejs/plugin-legacy if you need to support older browsers
    // https://github.com/vitejs/vite/tree/main/packages/plugin-legacy

    // Prints all the html entries needed for Vite

    function vite(string $entry)
    {
        $vite_host = VITE_HOST;
        if (isDev($entry)) {
            return <<<HTML
                <script type="module">
                    import RefreshRuntime from "{$vite_host}/@react-refresh"
                    window.\$RefreshReg$ = () => {}
                    window.\$RefreshSig$ = () => (type) => type
                    RefreshRuntime.injectIntoGlobalHook(window)
                    window.__vite_plugin_react_preamble_installed__ = true
                </script>
                <script type="module" src="{$vite_host}/@vite/client"></script>
                <!-- <script type="module" src="{$vite_host}/assets/js/website.js"></script> -->
                <script type="module" src="{$vite_host}/{$entry}" defer></script>
            HTML;
        } else {
            return "\n" . jsTag($entry)
                . "\n" . jsPreloadImports($entry)
                . "\n" . cssTag($entry);
        }
    }


    // Some dev/prod mechanism would exist in your project

    function isDev(string $entry): bool
    {
        // This method is very useful for the local server
        // if we try to access it, and by any means, didn't started Vite yet
        // it will fallback to load the production files from manifest
        // so you still navigate your site as you intended!

        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        $handle = curl_init(VITE_HOST . '/' . $entry);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_NOBODY, true);

        curl_exec($handle);
        $error = curl_errno($handle);
        curl_close($handle);

        return $exists = !$error;
    }


    // Helpers to print tags
    function jsTag(string $entry): string
    {
        $url = isDev($entry)
            ? VITE_HOST . '/' . $entry
            : assetUrl($entry);

        if (!$url) {
            return '';
        }
        if (isDev($entry)) {
            return '<script type="module" src="' . VITE_HOST . '/@vite/client"></script>' . "\n"
                . '<script type="module" src="' . $url . '"></script>';
        }
        return '<script type="module" src="' . $url . '"></script>';
    }

    function jsPreloadImports(string $entry): string
    {
        if (isDev($entry)) {
            return '';
        }

        $res = '';
        foreach (importsUrls($entry) as $url) {
            $res .= '<link rel="modulepreload" href="'
                . $url
                . '">';
        }
        return $res;
    }

    function cssTag(string $entry): string
    {
        // not needed on dev, it's inject by Vite
        if (isDev($entry)) {
            return '';
        }

        $tags = '';
        foreach (cssUrls($entry) as $url) {
            $tags .= '<link rel="stylesheet" href="'
                . $url
                . '">';
        }
        return $tags;
    }


    // Helpers to locate files
    function getManifest(): array
    {
        $content = file_get_contents(__DIR__ . '/dist/.vite/manifest.json');
        return json_decode($content, true);
    }

    function assetUrl(string $entry): string
    {
        $manifest = getManifest();

        return isset($manifest[$entry])
            ? '/dist/' . $manifest[$entry]['file']
            : '';
    }

    function importsUrls(string $entry): array
    {
        $urls = [];
        $manifest = getManifest();

        if (!empty($manifest[$entry]['imports'])) {
            foreach ($manifest[$entry]['imports'] as $imports) {
                $urls[] = '/dist/' . $manifest[$imports]['file'];
            }
        }
        return $urls;
    }

    function cssUrls(string $entry): array
    {
        $urls = [];
        $manifest = getManifest();

        if (!empty($manifest[$entry]['css'])) {
            foreach ($manifest[$entry]['css'] as $file) {
                $urls[] = '/dist/' . $file;
            }
        }
        return $urls;
    }
?>

<title>PIAST</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=0.7">
<link rel="shortcut icon" href="../assets/icons/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
<?php
    css('../assets/css/main.css');
    css('../assets/css/navbar.css');
    css('../assets/css/subnavbar.css');
    css('../assets/css/tab.css');
?>
<script src="../assets/js/jquery-3.6.3.min.js"></script>
<script>
    let alert_timer = null;
    let fadeout_before = false;

    function calcVh(){
        let vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    function alert_float(msg, success = false){
        const wrapper = $('#alert-window-wrapper');
        wrapper.show();
        wrapper.removeClass('fail success').addClass(success ? 'success' : 'fail');

        if(!fadeout_before){
            wrapper.addClass('fade-out1');
            fadeout_before = true;
        } else {
            wrapper.toggleClass('fade-out1 fade-out2');
        }

        $('#alert-msg').html(msg);
        $('#alert-icon').html(success ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-x-lg"></i>');
        
        if(alert_timer !== null) clearTimeout(alert_timer);
        alert_timer = setTimeout(function(){
            wrapper.hide();
        }, 3000);

        wrapper.one('click', function(){
            wrapper.hide();
        });
    }

    calcVh();
    window.addEventListener('resize', () => calcVh());
</script>
<div id="alert-window-wrapper" style="display: none">
    <div id="alert-window">
        <div id="alert-icon" id="alert-icon">
        </div>
        <div id="alert-msg">
        </div>
    </div>
</div>