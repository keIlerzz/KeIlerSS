<?php
error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 0);

$branco = "\e[97m";
$preto = "\e[30m\e[1m";
$amarelo = "\e[93m";
$laranja = "\e[38;5;208m";
$azul   = "\e[34m";
$lazul  = "\e[36m";
$cln    = "\e[0m";
$verde  = "\e[92m";
$fverde = "\e[32m";
$vermelho    = "\e[91m";
$magenta = "\e[35m";
$azulbg = "\e[44m";
$lazulbg = "\e[106m";
$verdebg = "\e[42m";
$lverdebg = "\e[102m";
$amarelobg = "\e[43m";
$lamarelobg = "\e[103m";
$vermelhobg = "\e[101m";
$cinza = "\e[37m";
$ciano = "\e[36m";
$bold   = "\e[1m";

function banner_keller(){
    echo "\e[92m
             $$$$\"

\e[36m{C} Codificado Por - AucerolaSS | KellerSS | SheikSS
\e[32m\n";
}

echo $cln;

function atualizar()
{
    global $cln, $bold, $fverde;
    echo "\n\e[91m\e[1m[+] AucerolaSS Updater [+]\nAtualizando, por favor aguarde...\n\n$cln";
    system("git fetch origin && git reset --hard origin/master && git clean -f -d");
    echo $bold . $fverde . "[i] Atualização concluida! Por favor reinicie o Scanner \n" . $cln;
    exit;
}

function detectarBypassShell() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    $bypassDetectado = false;
    
    echo $bold . $azul . "[+] Verificando funções maliciosas no ambiente shell...\n";
    
    $funcoesTeste = [
        'pkg' => 'adb shell "type pkg 2>/dev/null | grep -q function && echo FUNCTION_DETECTED"',
        'git' => 'adb shell "type git 2>/dev/null | grep -q function && echo FUNCTION_DETECTED"', 
        'cd' => 'adb shell "type cd 2>/dev/null | grep -q function && echo FUNCTION_DETECTED"',
        'stat' => 'adb shell "type stat 2>/dev/null | grep -q function && echo FUNCTION_DETECTED"',
        'adb' => 'adb shell "type adb 2>/dev/null | grep -q function && echo FUNCTION_DETECTED"'
    ];
    $executouChecksExtra = false;
    
    foreach ($funcoesTeste as $funcao => $comando) {
        $resultado = shell_exec($comando);
        if ($resultado !== null && strpos($resultado, 'FUNCTION_DETECTED') !== false) {
            echo $bold . $vermelho . "[!] BYPASS DETECTADO: Função '$funcao' foi sobrescrita!\n";
            $bypassDetectado = true;
        }
     
     if (!$executouChecksExtra) {
         echo $bold . $azul . "[+] Testando acesso a diretórios críticos...\n";
         
         $diretoriosCriticos = [
             '/system/bin',
             '/data/data/com.dts.freefireth/files',
             '/data/data/com.dts.freefiremax/files',
             '/storage/emulated/0/Android/data'
         ];
         
         foreach ($diretoriosCriticos as $diretorio) {
             $comandoTestDir = 'adb shell "ls -la \"' . $diretorio . '\" 2>/dev/null | head -3"';
             $resultadoTestDir = shell_exec($comandoTestDir);
             
             if ($resultadoTestDir === null || trim($resultadoTestDir) === '' || 
                 strpos($resultadoTestDir, 'Permission denied') !== false ||
                 strpos($resultadoTestDir, 'blocked') !== false ||
                 strpos($resultadoTestDir, 'redirected') !== false) {
                 
                 if (strpos($resultadoTestDir, 'blocked') !== false ||
                     strpos($resultadoTestDir, 'redirected') !== false ||
                     strpos($resultadoTestDir, 'bypass') !== false) {
                     
                     echo $bold . $vermelho . "[!] BYPASS DETECTADO: Acesso bloqueado/redirecionado ao diretório: $diretorio\n";
                     echo $bold . $amarelo . "[!] Resposta: " . trim($resultadoTestDir) . "\n";
                     $bypassDetectado = true;
                 }
             }
         }
         
         echo $bold . $azul . "[+] Verificando processos suspeitos...\n";
         
     $comandoProcessos = 'adb shell "ps | grep -E \"(bypass|redirect|fake)\" | grep -vE \"(drm_fake_vsync|mtk_drm_fake_vsync|mtk_drm_fake_vs)\" 2>/dev/null"';
         $resultadoProcessos = shell_exec($comandoProcessos);
         
         if ($resultadoProcessos !== null && !empty(trim($resultadoProcessos))) {
             $linhasProcessos = explode("\n", trim($resultadoProcessos));
             $processosSuspeitos = [];
             
             foreach ($linhasProcessos as $linha) {
             if (!empty(trim($linha)) && 
                 strpos($linha, '[kblockd]') === false && 
                 strpos($linha, 'kworker') === false &&
                 strpos($linha, '[ksoftirqd]') === false &&
                 strpos($linha, '[migration]') === false &&
                 strpos($linha, 'mtk_drm_fake_vsync') === false &&
                 strpos($linha, 'mtk_drm_fake_vs') === false &&
                 strpos($linha, 'drm_fake_vsync') === false) {
                 $processosSuspeitos[] = $linha;
             }
             }
             
             if (!empty($processosSuspeitos)) {
                 echo $bold . $vermelho . "[!] BYPASS DETECTADO: Processos suspeitos em execução!\n";
                 echo $bold . $amarelo . "[!] Processos encontrados:\n" . implode("\n", $processosSuspeitos) . "\n";
                 $bypassDetectado = true;
             }
         }
         $executouChecksExtra = true;
     }
    }
    
    echo $bold . $azul . "[+] Verificando arquivos de configuração...\n";
    $arquivosConfig = [
        '~/.bashrc', '~/.bash_profile', '~/.profile', '~/.zshrc', 
        '~/.config/fish/config.fish', '/data/data/com.termux/files/usr/etc/bash.bashrc'
    ];
    
    foreach ($arquivosConfig as $arquivo) {
        $comandoVerificar = 'adb shell "if [ -f ' . $arquivo . ' ]; then cat ' . $arquivo . ' | grep -E \"(function pkg|function git|function cd|function stat|function adb)\" 2>/dev/null; fi"';
        $resultadoArquivo = shell_exec($comandoVerificar);
        
        if ($resultadoArquivo !== null && !empty(trim($resultadoArquivo))) {
            echo $bold . $vermelho . "[!] BYPASS DETECTADO: Funções maliciosas em $arquivo!\n";
            echo $bold . $amarelo . "[!] Conteúdo detectado:\n" . trim($resultadoArquivo) . "\n";
            $bypassDetectado = true;
        }
    }
    
     echo $bold . $azul . "[+] Testando comportamento real das funções...\n";
     
     $comandoTestGitReal = 'adb shell "cd /tmp 2>/dev/null || cd /data/local/tmp; git clone --help 2>&1 | head -1"';
     $resultadoGitHelp = shell_exec($comandoTestGitReal);
     
     if ($resultadoGitHelp === null || empty($resultadoGitHelp) || strpos($resultadoGitHelp, 'usage: git') === false) {
         $comandoTestClone = 'adb shell "cd /tmp 2>/dev/null || cd /data/local/tmp; timeout 5 git clone https://github.com/kellerzz/KellerSS-Android test-repo 2>&1 | head -3"';
         $resultadoClone = shell_exec($comandoTestClone);
         
         if ($resultadoClone !== null && (strpos($resultadoClone, 'wendell77x') !== false || 
             strpos($resultadoClone, 'Comando bloqueado') !== false ||
             strpos($resultadoClone, 'blocked') !== false)) {
             echo $bold . $vermelho . "[!] BYPASS DETECTADO: Git clone sendo redirecionado!\n";
             echo $bold . $amarelo . "[!] Resposta: " . trim($resultadoClone) . "\n";
             $bypassDetectado = true;
         }
     }
     
     $comandoTestPkgReal = 'adb shell "pkg --help 2>&1 | head -1"';
     $resultadoPkgHelp = shell_exec($comandoTestPkgReal);
     
     if ($resultadoPkgHelp === null || empty($resultadoPkgHelp) || strpos($resultadoPkgHelp, 'Usage:') === false) {
         $comandoTestPkgInstall = 'adb shell "timeout 3 pkg install --help 2>&1"';
         $resultadoPkgInstall = shell_exec($comandoTestPkgInstall);
         
         if ($resultadoPkgInstall !== null && (strpos($resultadoPkgInstall, 'Comando bloqueado') !== false ||
             strpos($resultadoPkgInstall, 'blocked') !== false ||
             empty(trim($resultadoPkgInstall)))) {
             echo $bold . $vermelho . "[!] BYPASS DETECTADO: Comando pkg sendo bloqueado!\n";
             echo $bold . $amarelo . "[!] Resposta: " . trim($resultadoPkgInstall) . "\n";
             $bypassDetectado = true;
         }
     }
    
     echo $bold . $azul . "[+] Testando manipulação da função stat...\n";
     
     $arquivoTeste = '/data/local/tmp/test_stat_' . time();
     $comandoCriarArquivo = 'adb shell "echo test > ' . $arquivoTeste . ' 2>/dev/null"';
     shell_exec($comandoCriarArquivo);
     
     sleep(1);
     $comandoStatTeste = 'adb shell "stat ' . $arquivoTeste . ' 2>/dev/null"';
     $resultadoStatTeste = shell_exec($comandoStatTeste);
     
     if ($resultadoStatTeste !== null && !empty($resultadoStatTeste)) {
         preg_match('/Access: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStatTeste, $matchAccess);
         preg_match('/Modify: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStatTeste, $matchModify);
         preg_match('/Change: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStatTeste, $matchChange);
         
         if ($matchAccess && $matchModify && $matchChange) {
             $timestampAccess = strtotime($matchAccess[1]);
             $timestampModify = strtotime($matchModify[1]);
             $timestampChange = strtotime($matchChange[1]);
             $timestampAtual = time();
             
             $diferencaAtual = abs($timestampAtual - $timestampModify);
             $diferencaInterna = abs($timestampAccess - $timestampModify);
             
             if ($diferencaAtual > 86400 || $diferencaInterna > 300) {
                 echo $bold . $vermelho . "[!] BYPASS DETECTADO: Função stat retornando dados inconsistentes!\n";
                 echo $bold . $amarelo . "[!] Arquivo criado agora, mas stat mostra: " . $matchModify[1] . "\n";
                 $bypassDetectado = true;
             }
         }
     }
     
     shell_exec('adb shell "rm -f ' . $arquivoTeste . ' 2>/dev/null"');
     
     $caminhoMReplays = '/storage/emulated/0/Android/data/com.dts.freefireth/files/MReplays';
     $comandoStatMReplays = 'adb shell "stat ' . escapeshellarg($caminhoMReplays) . ' 2>/dev/null"';
     $resultadoStatMReplays = shell_exec($comandoStatMReplays);
     
     if ($resultadoStatMReplays !== null && !empty($resultadoStatMReplays) && preg_match('/Modify: (\d{4}-\d{2}-\d{2})/', $resultadoStatMReplays, $matches)) {
         $dataModify = $matches[1];
         if ($dataModify === '2020-01-01' || strtotime($dataModify) < strtotime('2021-01-01')) {
             echo $bold . $vermelho . "[!] BYPASS DETECTADO: Stat retornando data suspeita para MReplays!\n";
             echo $bold . $amarelo . "[!] Data suspeita: $dataModify\n";
             $bypassDetectado = true;
         }
     }
    
     echo $bold . $azul . "[+] Testando comportamento do comando cd...\n";
     
     $comandoTestCd = 'adb shell "cd /tmp 2>/dev/null || cd /data/local/tmp; pwd; cd /; pwd"';
     $resultadoTestCd = shell_exec($comandoTestCd);
     
     if ($resultadoTestCd === null || empty($resultadoTestCd) || strpos($resultadoTestCd, '/') === false) {
         echo $bold . $vermelho . "[!] BYPASS DETECTADO: Comando cd não está funcionando normalmente!\n";
         echo $bold . $amarelo . "[!] Resposta: " . trim($resultadoTestCd) . "\n";
         $bypassDetectado = true;
     }
     
     echo $bold . $azul . "[+] Testando integridade de comandos básicos...\n";
     
     $testesComandos = [
         'which' => ['adb shell "which ls 2>/dev/null"', '/system/bin/ls'],
         'echo' => ['adb shell "echo test123"', 'test123'],
         'date' => ['adb shell "date +%Y 2>/dev/null"', date('Y')]
     ];
     
     foreach ($testesComandos as $comando => $teste) {
         $resultado = trim(shell_exec($teste[0]));
         if ($resultado === null || empty($resultado) || strpos($resultado, $teste[1]) === false) {
             echo $bold . $vermelho . "[!] BYPASS DETECTADO: Comando '$comando' não retorna resposta esperada!\n";
             echo $bold . $amarelo . "[!] Esperado: {$teste[1]}, Recebido: $resultado\n";
             $bypassDetectado = true;
         }
     }
    
    echo $bold . $azul . "[+] Testando bloqueio de comandos pkg...\n";
    $comandoTestPkg = 'adb shell "echo \"pkg install com.dts.freefireth\" | bash 2>&1"';
    $resultadoTestPkg = shell_exec($comandoTestPkg);
    
    if ($resultadoTestPkg !== null && (strpos($resultadoTestPkg, 'Comando bloqueado') !== false || 
        strpos($resultadoTestPkg, 'blocked') !== false)) {
        echo $bold . $vermelho . "[!] BYPASS DETECTADO: Bloqueio de comandos pkg ativo!\n";
        echo $bold . $amarelo . "[!] Resposta do sistema: " . trim($resultadoTestPkg) . "\n";
        $bypassDetectado = true;
    }
    
     echo $bold . $azul . "[+] Verificando arquivos de bypass no dispositivo...\n";
     
     $comandoArquivosBypass = 'adb shell "find /sdcard /data/local/tmp /data/data/com.termux/files/home -name \"*.sh\" -exec grep -l \"function pkg\\|function git\\|function cd\\|function stat\\|function adb\\|wendell77x\\|FAKE_ADB_SHELL\" {} \\; 2>/dev/null | head -10"';
     $resultadoArquivosBypass = shell_exec($comandoArquivosBypass);
     
     if ($resultadoArquivosBypass !== null && !empty(trim($resultadoArquivosBypass))) {
         echo $bold . $vermelho . "[!] BYPASS DETECTADO: Arquivos de bypass encontrados!\n";
         echo $bold . $amarelo . "[!] Arquivos suspeitos:\n" . trim($resultadoArquivosBypass) . "\n";
         $bypassDetectado = true;
     }
     
     $comandoNomesSuspeitos = 'adb shell "find /sdcard /data/local/tmp /data/data/com.termux/files/home -name \"*block*\" -o -name \"*redirect*\" -o -name \"*bypass*\" -o -name \"*install*\" -o -name \"*hack*\" 2>/dev/null | head -10"';
     $resultadoNomesSuspeitos = shell_exec($comandoNomesSuspeitos);
     
     if ($resultadoNomesSuspeitos !== null && !empty(trim($resultadoNomesSuspeitos))) {
         echo $bold . $vermelho . "[!] BYPASS DETECTADO: Arquivos com nomes suspeitos encontrados!\n";
         echo $bold . $amarelo . "[!] Arquivos encontrados:\n" . trim($resultadoNomesSuspeitos) . "\n";
         $bypassDetectado = true;
     }
    
    if ($bypassDetectado) {
        echo $bold . $vermelho . "\n[!] ========== ATENÇÃO ==========\n";
        echo $bold . $vermelho . "[!] BYPASS DE FUNÇÕES SHELL DETECTADO!\n";
        echo $bold . $vermelho . "[!] O usuário está utilizando scripts maliciosos!\n";
        echo $bold . $vermelho . "[!] APLIQUE O W.O IMEDIATAMENTE!\n";
        echo $bold . $vermelho . "[!] ==============================\n\n";
    } else {
        echo $bold . $fverde . "[i] Nenhum bypass de funções shell detectado.\n\n";
    }
}

function verificarAcessoUso() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando configurações de acesso ao uso...\n";
    
    $comandoUsageAccess = 'adb shell "settings get secure usage_stats_enabled"';
    $resultadoUsageAccess = trim(shell_exec($comandoUsageAccess));
    
    if ($resultadoUsageAccess === "0") {
        echo $bold . $vermelho . "[!] BYPASS DETECTADO: Acesso ao uso desativado!\n";
        echo $bold . $amarelo . "[!] O usuário desativou o acesso às estatísticas de uso para evitar detecção\n";
        return true;
    } elseif ($resultadoUsageAccess === "1") {
        echo $bold . $fverde . "[i] Acesso ao uso está ativado normalmente\n";
    } else {
        echo $bold . $amarelo . "[!] Não foi possível verificar o acesso ao uso\n";
    }
    
    $comandoUsageStats = 'adb shell "dumpsys usagestats | head -20"';
    $resultadoUsageStats = shell_exec($comandoUsageStats);
    
    if ($resultadoUsageStats === null || empty(trim($resultadoUsageStats))) {
        echo $bold . $vermelho . "[!] BYPASS DETECTADO: Estatísticas de uso vazias ou bloqueadas!\n";
        return true;
    }
    
    return false;
}

function verificarGerenciadoresArquivos() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando abertura recente de gerenciadores de arquivos...\n";
    
    $gerenciadores = [
        'brevent' => 'com.brevent',
        'zarchiver' => 'ru.zdevs.zarchiver',
        'mixplorer' => 'com.mixplorer',
        'solid_explorer' => 'pl.solidexplorer2',
        'fx_file' => 'nextapp.fx',
        'total_commander' => 'com.ghisler.tcplugins',
        'es_file' => 'com.estrongs.android.pop',
        'xplore' => 'com.lonelycatgames.Xplore'
    ];
    
    $encontrouSuspeito = false;
    
    foreach ($gerenciadores as $nome => $pacote) {
        $comando = "adb shell dumpsys usagestats 2>/dev/null | grep -i '$pacote' | head -5";
        $resultado = shell_exec($comando);
        
        if ($resultado !== null && !empty(trim($resultado))) {
            echo $bold . $vermelho . "[!] $nome foi aberto recentemente!\n";
            echo $bold . $amarelo . "[!] Atividade detectada:\n" . trim($resultado) . "\n";
            $encontrouSuspeito = true;
        }
    }
    
    $comandoTodosApps = 'adb shell "dumpsys usagestats 2>/dev/null | grep -E \"(MOVE_TO_FOREGROUND|MOVE_TO_BACKGROUND)\" | grep -v \"com.android.vending\" | grep -v \"com.dts.freefire\" | tail -10"';
    $resultadoTodosApps = shell_exec($comandoTodosApps);
    
    if ($resultadoTodosApps !== null && !empty(trim($resultadoTodosApps))) {
        $linhas = explode("\n", trim($resultadoTodosApps));
        foreach ($linhas as $linha) {
            if (strpos($linha, 'file') !== false || strpos($linha, 'explorer') !== false || 
                strpos($linha, 'manager') !== false || strpos($linha, 'archive') !== false) {
                echo $bold . $vermelho . "[!] Possível gerenciador de arquivos detectado:\n";
                echo $bold . $amarelo . "[!] $linha\n";
                $encontrouSuspeito = true;
            }
        }
    }
    
    if (!$encontrouSuspeito) {
        echo $bold . $fverde . "[i] Nenhum gerenciador de arquivos suspeito detectado recentemente\n";
    }
    
    return $encontrouSuspeito;
}

function verificarPermissoesSuspeitas() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando permissões suspeitas...\n";
    
    $permissoesSuspeitas = [
        'android.permission.WRITE_SECURE_SETTINGS',
        'android.permission.PACKAGE_USAGE_STATS',
        'android.permission.ACCESS_USAGE_STATS',
        'android.permission.WRITE_SETTINGS'
    ];
    
    $encontrouSuspeito = false;
    
    foreach ($permissoesSuspeitas as $permissao) {
        $comando = "adb shell pm list permissions | grep '$permissao'";
        $resultado = shell_exec($comando);
        
        if ($resultado !== null && !empty(trim($resultado))) {
            echo $bold . $amarelo . "[!] Permissão suspeita encontrada: $permissao\n";
            $encontrouSuspeito = true;
        }
    }
    
    $comandoAppsComPermissao = "adb shell pm list packages -f | grep -E '(brevent|zarchiver|xplore|mixplorer)'";
    $resultadoAppsComPermissao = shell_exec($comandoAppsComPermissao);
    
    if ($resultadoAppsComPermissao !== null && !empty(trim($resultadoAppsComPermissao))) {
        echo $bold . $vermelho . "[!] Apps com permissões suspeitas instalados:\n";
        echo $bold . $amarelo . trim($resultadoAppsComPermissao) . "\n";
        $encontrouSuspeito = true;
    }
    
    if (!$encontrouSuspeito) {
        echo $bold . $fverde . "[i] Nenhuma permissão suspeita detectada\n";
    }
    
    return $encontrouSuspeito;
}

function verificarModulosMagisk() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando módulos Magisk suspeitos...\n";
    
    $modulosSuspeitos = [
        'riru' => 'riru',
        'lsposed' => 'lsposed',
        'edxposed' => 'edxposed',
        'xposed' => 'xposed',
        'magiskhide' => 'magiskhide',
        'zygisk' => 'zygisk'
    ];
    
    $encontrouSuspeito = false;
    
    foreach ($modulosSuspeitos as $nome => $modulo) {
        $comando = "adb shell su -c 'find /data/adb/modules -name \"*$modulo*\" -type d 2>/dev/null'";
        $resultado = shell_exec($comando);
        
        if ($resultado !== null && !empty(trim($resultado))) {
            echo $bold . $vermelho . "[!] Módulo Magisk suspeito encontrado: $nome\n";
            echo $bold . $amarelo . "[!] Caminho: " . trim($resultado) . "\n";
            $encontrouSuspeito = true;
        }
    }
    
    $comandoMagiskModules = "adb shell su -c 'ls /data/adb/modules 2>/dev/null'";
    $resultadoMagiskModules = shell_exec($comandoMagiskModules);
    
    if ($resultadoMagiskModules !== null && !empty(trim($resultadoMagiskModules))) {
        echo $bold . $vermelho . "[!] Módulos Magisk instalados:\n";
        echo $bold . $amarelo . trim($resultadoMagiskModules) . "\n";
        $encontrouSuspeito = true;
    }
    
    if (!$encontrouSuspeito) {
        echo $bold . $fverde . "[i] Nenhum módulo Magisk suspeito detectado\n";
    }
    
    return $encontrouSuspeito;
}

function verificarAppsSuspeitos() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando apps de cheat/hack instalados...\n";
    
    $appsSuspeitos = [
        'gameguardian' => 'com.gameguardian',
        'lucky_patcher' => 'com.chelpus.lackypatch',
        'parallel_space' => 'com.lbe.parallel',
        'virtualxposed' => 'io.va.exposed',
        'fgl_pro' => 'com.fglpro',
        'gg_script' => 'org.gg.script',
        'memory_editor' => 'com.memory.editor',
        'hack_game' => 'com.hack.game',
        'cheat_engine' => 'com.cheat.engine',
        'gamecih' => 'com.gamecih',
        'freedom' => 'com.phone.freedom',
        'creehack' => 'com.creehack'
    ];
    
    $encontrouSuspeito = false;
    
    foreach ($appsSuspeitos as $nome => $pacote) {
        $comando = "adb shell pm list packages | grep '$pacote'";
        $resultado = shell_exec($comando);
        
        if ($resultado !== null && !empty(trim($resultado))) {
            echo $bold . $vermelho . "[!] App de cheat detectado: $nome\n";
            echo $bold . $amarelo . "[!] Pacote: " . trim($resultado) . "\n";
            $encontrouSuspeito = true;
        }
    }
    
    $comandoTodosApps = "adb shell pm list packages -3";
    $resultadoTodosApps = shell_exec($comandoTodosApps);
    
    if ($resultadoTodosApps !== null && !empty(trim($resultadoTodosApps))) {
        $apps = explode("\n", trim($resultadoTodosApps));
        foreach ($apps as $app) {
            if (strpos($app, 'hack') !== false || strpos($app, 'cheat') !== false || 
                strpos($app, 'mod') !== false || strpos($app, 'game') !== false &&
                strpos($app, 'freefire') === false) {
                echo $bold . $amarelo . "[!] App suspeito instalado: $app\n";
                $encontrouSuspeito = true;
            }
        }
    }
    
    if (!$encontrouSuspeito) {
        echo $bold . $fverde . "[i] Nenhum app de cheat detectado\n";
    }
    
    return $encontrouSuspeito;
}

function verificarProcessosSuspeitos() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando processos suspeitos em execução...\n";
    
    $processosSuspeitos = [
        'gameguardian',
        'gg',
        'lucky',
        'parallel',
        'xposed',
        'edxposed',
        'lsposed',
        'memory',
        'hack',
        'cheat',
        'fgl',
        'cih'
    ];
    
    $encontrouSuspeito = false;
    
    foreach ($processosSuspeitos as $processo) {
        $comando = "adb shell ps | grep -i '$processo'";
        $resultado = shell_exec($comando);
        
        if ($resultado !== null && !empty(trim($resultado))) {
            echo $bold . $vermelho . "[!] Processo suspeito em execução: $processo\n";
            echo $bold . $amarelo . "[!] Detalhes:\n" . trim($resultado) . "\n";
            $encontrouSuspeito = true;
        }
    }
    
    $comandoTodosProcessos = "adb shell ps | grep -v 'system\\|kernel'";
    $resultadoTodosProcessos = shell_exec($comandoTodosProcessos);
    
    if ($resultadoTodosProcessos !== null && !empty(trim($resultadoTodosProcessos))) {
        $processos = explode("\n", trim($resultadoTodosProcessos));
        foreach ($processos as $processo) {
            if (strpos($processo, 'game') !== false && strpos($processo, 'freefire') === false ||
                strpos($processo, 'tool') !== false || strpos($processo, 'script') !== false) {
                echo $bold . $amarelo . "[!] Processo potencialmente suspeito:\n$processo\n";
                $encontrouSuspeito = true;
            }
        }
    }
    
    if (!$encontrouSuspeito) {
        echo $bold . $fverde . "[i] Nenhum processo suspeito detectado\n";
    }
    
    return $encontrouSuspeito;
}

function verificarArquivosSuspeitos() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando arquivos de cheat no sistema...\n";
    
    $arquivosSuspeitos = [
        '/sdcard/GameGuardian',
        '/sdcard/GG',
        '/sdcard/LuckyPatcher',
        '/sdcard/Parallel',
        '/sdcard/Xposed',
        '/sdcard/cheat',
        '/sdcard/hack',
        '/sdcard/mod',
        '/sdcard/script',
        '/sdcard/gg.txt',
        '/sdcard/hack.txt',
        '/data/local/tmp/gg',
        '/data/local/tmp/hack',
        '/data/local/tmp/cheat'
    ];
    
    $encontrouSuspeito = false;
    
    foreach ($arquivosSuspeitos as $arquivo) {
        $comando = "adb shell ls -la '$arquivo' 2>/dev/null";
        $resultado = shell_exec($comando);
        
        if ($resultado !== null && !empty(trim($resultado))) {
            echo $bold . $vermelho . "[!] Arquivo suspeito encontrado: $arquivo\n";
            echo $bold . $amarelo . "[!] Conteúdo do diretório:\n" . trim($resultado) . "\n";
            $encontrouSuspeito = true;
        }
    }
    
    $comandoBuscaArquivos = "adb shell find /sdcard /data/local/tmp -name '*hack*' -o -name '*cheat*' -o -name '*gg*' -o -name '*mod*' -o -name '*script*' 2>/dev/null | head -20";
    $resultadoBuscaArquivos = shell_exec($comandoBuscaArquivos);
    
    if ($resultadoBuscaArquivos !== null && !empty(trim($resultadoBuscaArquivos))) {
        echo $bold . $vermelho . "[!] Arquivos suspeitos encontrados no sistema:\n";
        echo $bold . $amarelo . trim($resultadoBuscaArquivos) . "\n";
        $encontrouSuspeito = true;
    }
    
    if (!$encontrouSuspeito) {
        echo $bold . $fverde . "[i] Nenhum arquivo de cheat detectado\n";
    }
    
    return $encontrouSuspeito;
}

function verificarConfiguracoesDesenvolvedor() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando configurações de desenvolvedor suspeitas...\n";
    
    $configuracoesSuspeitas = [
        'adb_enabled' => 'adb shell settings get global adb_enabled',
        'development_settings_enabled' => 'adb shell settings get global development_settings_enabled',
        'usb_debugging' => 'adb shell settings get global usb_debugging',
        'install_non_market_apps' => 'adb shell settings get global install_non_market_apps'
    ];
    
    $encontrouSuspeito = false;
    
    foreach ($configuracoesSuspeitas as $nome => $comando) {
        $resultado = trim(shell_exec($comando));
        
        if ($resultado === "1") {
            echo $bold . $amarelo . "[!] Configuração suspeita ativada: $nome\n";
            $encontrouSuspeito = true;
        }
    }
    
    $comandoBuildProp = "adb shell getprop ro.debuggable";
    $resultadoBuildProp = trim(shell_exec($comandoBuildProp));
    
    if ($resultadoBuildProp === "1") {
        echo $bold . $vermelho . "[!] BUILD CONFIGURADO COMO DEBUGGABLE - POSSÍVEL ROM MODIFICADA!\n";
        $encontrouSuspeito = true;
    }
    
    if (!$encontrouSuspeito) {
        echo $bold . $fverde . "[i] Configurações de desenvolvedor normais\n";
    }
    
    return $encontrouSuspeito;
}

function verificarTweaksPerformance() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando tweaks de performance suspeitos...\n";
    
    $tweaksSuspeitos = [
        '/system/etc/init.d',
        '/system/su.d',
        '/system/xbin/su',
        '/system/bin/su',
        '/data/adb/service.d',
        '/data/adb/post-fs-data.d'
    ];
    
    $encontrouSuspeito = false;
    
    foreach ($tweaksSuspeitos as $tweak) {
        $comando = "adb shell ls -la '$tweak' 2>/dev/null";
        $resultado = shell_exec($comando);
        
        if ($resultado !== null && !empty(trim($resultado))) {
            echo $bold . $vermelho . "[!] Tweak de performance suspeito encontrado: $tweak\n";
            echo $bold . $amarelo . "[!] Conteúdo:\n" . trim($resultado) . "\n";
            $encontrouSuspeito = true;
        }
    }
    
    $comandoScriptsInit = "adb shell find /system /data -name '*.sh' -path '*/init.d/*' -o -path '*/su.d/*' -o -path '*/service.d/*' 2>/dev/null | head -10";
    $resultadoScriptsInit = shell_exec($comandoScriptsInit);
    
    if ($resultadoScriptsInit !== null && !empty(trim($resultadoScriptsInit))) {
        echo $bold . $vermelho . "[!] Scripts de inicialização suspeitos encontrados:\n";
        echo $bold . $amarelo . trim($resultadoScriptsInit) . "\n";
        $encontrouSuspeito = true;
    }
    
    if (!$encontrouSuspeito) {
        echo $bold . $fverde . "[i] Nenhum tweak de performance suspeito detectado\n";
    }
    
    return $encontrouSuspeito;
}

function verificarVirtualizacao() {
    global $bold, $vermelho, $amarelo, $fverde, $azul, $branco, $cln;
    
    echo $bold . $azul . "[+] Verificando virtualização/emulação...\n";
    
    $indicadoresVirtualizacao = [
        'ro.build.fingerprint' => 'adb shell getprop ro.build.fingerprint',
        'ro.product.model' => 'adb shell getprop ro.product.model',
        'ro.product.manufacturer' => 'adb shell getprop ro.product.manufacturer',
        'ro.hardware' => 'adb shell getprop ro.hardware'
    ];
    
    $emuladoresConhecidos = [
        'bluestacks', 'nox', 'memu', 'ldplayer', 'genymotion', 'andy', 'amidu',
        'virtualbox', 'qemu', 'kvm', 'vmware', 'parallels'
    ];
    
    $encontrouSuspeito = false;
    
    foreach ($indicadoresVirtualizacao as $nome => $comando) {
        $resultado = strtolower(trim(shell_exec($comando)));
        
        foreach ($emuladoresConhecidos as $emulador) {
            if (strpos($resultado, $emulador) !== false) {
                echo $bold . $vermelho . "[!] POSSÍVEL EMULADOR DETECTADO: $emulador\n";
                echo $bold . $amarelo . "[!] Propriedade $nome: $resultado\n";
                $encontrouSuspeito = true;
            }
        }
    }
    
    $comandoCpuInfo = "adb shell cat /proc/cpuinfo";
    $resultadoCpuInfo = strtolower(shell_exec($comandoCpuInfo));
    
    if ($resultadoCpuInfo !== null && (strpos($resultadoCpuInfo, 'qemu') !== false || strpos($resultadoCpuInfo, 'virtualbox') !== false)) {
        echo $bold . $vermelho . "[!] VIRTUALIZAÇÃO DETECTADA VIA CPUINFO!\n";
        $encontrouSuspeito = true;
    }
    
    if (!$encontrouSuspeito) {
        echo $bold . $fverde . "[i] Nenhum indicador de virtualização detectado\n";
    }
    
    return $encontrouSuspeito;
}

function inputusuario($message){
  global $branco, $bold, $verdebg, $vermelhobg, $azulbg, $cln, $lazul, $fverde;
  $amarelobg = "\e[100m";
  $inputstyle = $cln . $bold . $lazul . "[#] " . $message . ": " . $fverde ;
echo $inputstyle;
}

system("clear");
keller_banner();
sleep(5);
echo "\n";

menuscanner:

    echo $bold . $azul . "
      +--------------------------------------------------------------+
      +                   AucerolaSS & KellerSS Menu                +
      +--------------------------------------------------------------+

      \n\n";
      echo $amarelo . " [0]  Conectar ADB$branco (Pareamento e conexão via ADB)$fverde \n [1]  Escanear FreeFire Normal \n$fverde [2]  Escanear FreeFire Max \n {$vermelho}[S]  Sair \n\n" . $cln;
escolheropcoes:
    inputusuario("Escolha uma das opções acima");
    $opcaoscanner = trim(fgets(STDIN, 1024));


    if (!in_array($opcaoscanner, array(
      '0',
      '1',
      '2',	
      'S',
  ), true))
    {
      echo $bold . $vermelho . "\n[!] Opção inválida! Tente novamente. \n\n" . $cln;
      goto escolheropcoes;
    }
    else
    {
        if ($opcaoscanner == "0") {
            system("clear");
            keller_banner();
            
            echo $bold . $azul . "[+] Verificando se o ADB está instalado...\n" . $cln;
            if (!shell_exec("adb version > /dev/null 2>&1"))
            {
                echo $bold . $amarelo . "[!] ADB não encontrado. Instalando android-tools...\n" . $cln;
                system("pkg install android-tools -y");
                echo $bold . $fverde . "[i] Android-tools instalado com sucesso!\n\n" . $cln;
            } else {
                echo $bold . $fverde . "[i] ADB já está instalado.\n\n" . $cln;
            }
            
            inputusuario("Qual a sua porta para o pareamento (ex: 45678)?");
            $pair_port = trim(fgets(STDIN, 1024));
            if (!empty($pair_port) && is_numeric($pair_port)) {
                echo $bold . $amarelo . "\n[!] Agora, digite o código de pareamento que aparece no seu celular e pressione Enter.\n" . $cln;
                system("adb pair localhost:" . $pair_port);
            } else {
                echo $bold . $vermelho . "\n[!] Porta inválida! Retornando ao menu.\n\n" . $cln;
                sleep(2);
                system("clear");
                keller_banner();
                goto menuscanner;
            }
            
            echo "\n";
            
            inputusuario("Qual a sua porta para a conexão (ex: 12345)?");
            $connect_port = trim(fgets(STDIN, 1024));
            if (!empty($connect_port) && is_numeric($connect_port)) {
                echo $bold . $amarelo . "\n[!] Conectando ao dispositivo...\n" . $cln;
                system("adb connect localhost:" . $connect_port);
                echo $bold . $fverde . "\n[i] Processo de conexão finalizado. Verifique a saída acima para ver se a conexão foi bem-sucedida.\n" . $cln;
                echo $bold . $branco . "\n[+] Pressione Enter para voltar ao menu...\n" . $cln;
                fgets(STDIN, 1024);
                system("clear");
                keller_banner();
                goto menuscanner;
            } else {
                echo $bold . $vermelho . "\n[!] Porta inválida! Retornando ao menu.\n\n" . $cln;
                sleep(2);
                system("clear");
                keller_banner();
                goto menuscanner;
            }
        } elseif ($opcaoscanner == "1") {
            system("clear");
            keller_banner();

            if (!shell_exec("adb version > /dev/null 2>&1"))
            {
                system("pkg install -y android-tools > /dev/null 2>&1");
            }

            date_default_timezone_set('America/Sao_Paulo');
            shell_exec('adb start-server > /dev/null 2>&1');

            $comandoDispositivos = shell_exec("adb devices 2>&1");

                if ($comandoDispositivos === null || empty($comandoDispositivos) || strpos($comandoDispositivos, "device") === false || strpos($comandoDispositivos, "no devices") !== false) {
                    echo "\033[1;31m[!] Nenhum dispositivo encontrado. Faça o pareamento de IP ou conecte um dispositivo via USB.\n\n";
                    exit;
                }

                $comandoVerificarFF = shell_exec("adb shell pm list packages --user 0 | grep com.dts.freefireth 2>&1");

                if ($comandoVerificarFF !== null && !empty($comandoVerificarFF) && strpos($comandoVerificarFF, "more than one device/emulator") !== false) {
                    echo $bold . $vermelho . "[!] Pareamento realizado de maneira incorreta, digite \"adb disconnect\" e refaça o processo.\n\n";
                    exit;
                }
                
                if ($comandoVerificarFF !== null && !empty($comandoVerificarFF) && strpos($comandoVerificarFF, "com.dts.freefireth") !== false) {
                } else {
                    echo $bold . $vermelho . "[!] O FreeFire está desinstalado, cancelando a telagem...\n\n";
                    exit;
                }

                $comandoVersaoAndroid = "adb shell getprop ro.build.version.release";
                $resultadoVersaoAndroid = shell_exec($comandoVersaoAndroid);

                if ($resultadoVersaoAndroid !== null && !empty($resultadoVersaoAndroid)) {
                    echo $bold . $azul . "[+] Versão do Android: " . trim($resultadoVersaoAndroid) . "\n";
                } else {
                    echo $bold . $vermelho . "[!] Não foi possível obter a versão do Android.\n";
                }

                $comandoSu = 'su 2>&1';
                $resultadoSu = shell_exec($comandoSu);

                echo $bold . $azul . "[+] Checando se possui Root (se o programa travar, root detectado)...\n";
                if ($resultadoSu !== null && !empty($resultadoSu) && strpos($resultadoSu, 'No su program found') !== false) {
                    echo $bold . $fverde . "[-] O dispositivo não tem root.\n\n";
                } else {
                    echo $bold . $vermelho . "[+] Root detectado no dispositivo Android.\n\n";
                }
                
            echo $bold . $azul . "[+] Verificando scripts ativos em segundo plano...\n";
            $comandoScripts = 'adb shell "pgrep -a bash | awk \'{\$1=\"\"; sub(/^ /,\"\"); print}\' | grep -vFx \"/data/data/com.termux/files/usr/bin/bash -l\""';
            $scriptsAtivos = shell_exec($comandoScripts);
            
            if ($scriptsAtivos !== null && trim($scriptsAtivos) !== '') {
                echo $bold . $vermelho . "[!] Scripts detectados rodando em segundo plano! Cancelando scanner...\n";
                echo $bold . $amarelo . "Scripts encontrados:\n" . trim($scriptsAtivos) . "\n\n";
                exit;
            }
            
            echo $bold . $fverde . "[i] Nenhum script ativo detectado.\n";
            echo $bold . $azul . "[+] Finalizando sessões bash desnecessárias...\n";
            $comandoKillBash = 'adb shell "current_pid=\$\$; for pid in \$(pgrep bash); do [ \"\$pid\" -ne \"\$current_pid\" ] && kill -9 \$pid; done"';
            shell_exec($comandoKillBash);
            echo $bold . $fverde . "[i] Sessões desnecessárias finalizadas.\n\n";

            echo $bold . $azul . "[+] Verificando bypasses de funções shell...\n";
            detectarBypassShell();

            echo $bold . $azul . "[+] Verificando configurações de acesso ao uso...\n";
            verificarAcessoUso();

            echo $bold . $azul . "[+] Verificando gerenciadores de arquivos...\n";
            verificarGerenciadoresArquivos();

            echo $bold . $azul . "[+] Verificando permissões suspeitas...\n";
            verificarPermissoesSuspeitas();

            echo $bold . $azul . "[+] Verificando módulos Magisk...\n";
            verificarModulosMagisk();

            echo $bold . $azul . "[+] Verificando apps de cheat...\n";
            verificarAppsSuspeitos();

            echo $bold . $azul . "[+] Verificando processos suspeitos...\n";
            verificarProcessosSuspeitos();

            echo $bold . $azul . "[+] Verificando arquivos de cheat...\n";
            verificarArquivosSuspeitos();

            echo $bold . $azul . "[+] Verificando configurações de desenvolvedor...\n";
            verificarConfiguracoesDesenvolvedor();

            echo $bold . $azul . "[+] Verificando tweaks de performance...\n";
            verificarTweaksPerformance();

            echo $bold . $azul . "[+] Verificando virtualização...\n";
            verificarVirtualizacao();

            echo $bold . $azul . "[+] Checando se o dispositivo foi reiniciado recentemente...\n";
            $comandoUPTIME = shell_exec("adb shell uptime");

            if ($comandoUPTIME !== null && preg_match('/up (\d+) min/', $comandoUPTIME, $filtros)) {
                $minutos = $filtros[1];
                echo $bold . $vermelho . "[!] O dispositivo foi iniciado recentemente (há $minutos minutos).\n\n";
            } else {
                echo $bold . $fverde . "[i] Dispositivo não reiniciado recentemente.\n\n";
            }

            $logcatTime = shell_exec("adb logcat -d -v time | head -n 2");
            if ($logcatTime !== null) {
                preg_match('/(\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $logcatTime, $matchTime);

                if (!empty($matchTime[1])) {

                    $date = DateTime::createFromFormat('m-d H:i:s', $matchTime[1]);
                    $formattedDate = $date->format('d-m H:i:s'); 

                    echo $bold . $amarelo . "[+] Primeira log do sistema: " . $formattedDate . "\n";
                    echo $bold . $branco . "[+] Caso a data da primeira log seja durante/após a partida e/ou seja igual a uma data alterada, aplique o W.O!\n\n";

                } else {
                    echo $bold . $vermelho . "[!] Não foi possível capturar a data/hora do sistema.\n\n";
                }
            }
            
            echo $bold . $azul . "[+] Verificando mudanças de data/hora...\n";

                
            $logcatOutput = shell_exec('adb logcat -d | grep "UsageStatsService: Time changed" | grep -v "HCALL"');

            if ($logcatOutput !== null && trim($logcatOutput) !== "") {
                $logLines = explode("\n", trim($logcatOutput));
            } else {
                echo $bold . $vermelho . "[!] Erro ao obter logs de modificação de data/hora, verifique a data da primeira log do sistema.\n\n";
            }

            $fusoHorario = trim(shell_exec('adb shell getprop persist.sys.timezone'));

            if ($fusoHorario !== "America/Sao_Paulo") {
                echo $bold . $amarelo . "[!] Aviso: O fuso horário do dispositivo é '$fusoHorario', diferente de 'America/Sao_Paulo', possivel tentativa de Bypass.\n\n";
            }

            $dataAtual = date("m-d");

            $logsAlterados = [];

            if (!empty($logLines)) {
                foreach ($logLines as $line) {
                    if (empty($line)) continue;

                    preg_match('/(\d{2}-\d{2}) (\d{2}:\d{2}:\d{2}\.\d{3}).*Time changed in.*by (-?\d+) second/', $line, $matches);

                    if (!empty($matches) && $matches[1] === $dataAtual) {
                        list($hora, $minuto, $segundoComDecimal) = explode(":", $matches[2]);
                        $segundo = (int)floor($segundoComDecimal);

                        $horaAntiga = mktime($hora, $minuto, $segundo, substr($matches[1], 0, 2), substr($matches[1], 3, 2), date("Y"));

                        $segundosAlterados = (int)$matches[3];

                        $horaNova = ($segundosAlterados > 0) ? $horaAntiga - $segundosAlterados : $horaAntiga + abs($segundosAlterados);

                        $dataAntiga = date("d-m H:i", $horaAntiga);
                        $horaAntigaFormatada = date("H:i", $horaAntiga);
                        $horaNovaFormatada = date("H:i", $horaNova);
                        $dataNova = date("d-m", $horaNova);

                        $logsAlterados[] = [
                            'horaAntiga' => $horaAntiga,
                            'horaNova' => $horaNova,
                            'horaAntigaFormatada' => $horaAntigaFormatada,
                            'horaNovaFormatada' => $horaNovaFormatada,
                            'acao' => ($segundosAlterados > 0) ? 'Atrasou' : 'Adiantou',
                            'dataAntiga' => $dataAntiga,
                            'dataNova' => $dataNova
                        ];
                    }
                }
            }

            if (!empty($logsAlterados)) {
                usort($logsAlterados, function ($a, $b) {
                    return $b['horaAntiga'] - $a['horaAntiga'];
                });

                foreach ($logsAlterados as $log) {
                    echo $bold . $amarelo . "[!] Alterou horário de {$log['dataAntiga']} para {$log['dataNova']} {$log['horaNovaFormatada']} ({$log['acao']} horário)\n";
                }
            } else {
                echo $bold . $vermelho . "[!] Nenhum log de alteração de horário encontrado.\n\n";
            }

        
            
            echo $bold . $azul . "\n[+] Checando se modificou data e hora...\n";
            $autoTime = trim(shell_exec('adb shell settings get global auto_time'));
            $autoTimeZone = trim(shell_exec('adb shell settings get global auto_time_zone'));

            if ($autoTime !== "1" || $autoTimeZone !== "1") {
                echo $bold . $vermelho . "[!] Possível bypass detectado: data e hora/furo horário automático desativado.\n";
            } else {
                echo $bold . $fverde . "[i] Data e hora/fuso horário automático estão ativados.\n";
            }

            echo $bold . $branco . "[+] Caso haja mudança de horário durante/após a partida, aplique o W.O!\n\n";

            echo $bold . $azul . "[+] Obtendo os últimos acessos do Google Play Store...\n";

            $comandoUSAGE = shell_exec("adb shell dumpsys usagestats 2>/dev/null | grep -i 'MOVE_TO_FOREGROUND' 2>/dev/null | grep 'package=com.android.vending' 2>/dev/null | awk -F'time=\"' '{print \$2}' 2>/dev/null | awk '{gsub(/\"/, \"\"); print \$1, \$2}' 2>/dev/null | tail -n 5 2>/dev/null");

            if ($comandoUSAGE !== null && trim($comandoUSAGE) !== "") {
                echo $bold . $fverde . "[i] Últimos 5 acessos:\n";
                echo $amarelo . $comandoUSAGE . "\n";
            } else {
                echo $bold . "\e[31m[!] Nenhum dado encontrado.\n";
            }
            echo $bold . $branco . "[+] Caso haja acesso durante/após a partida, aplique o W.O!\n\n";

            echo $bold . $azul . "[+] Obtendo os últimos textos copiados...\n";

            $comando = "adb logcat -d 2>/dev/null | grep 'hcallSetClipboardTextRpc' 2>/dev/null | sed -E 's/^([0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2}).*hcallSetClipboardTextRpc\\(([^)]*)\\).*$/\\1 \\2 \\3/' 2>/dev/null | tail -n 10 2>/dev/null";
            $saida = shell_exec($comando);

            if ($saida !== null) {
                $linhas = explode("\n", trim($saida));
                
                foreach ($linhas as $linha) {
                    if (!empty($linha) && preg_match('/^([0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2}) (.+)$/', $linha, $matches)) {
                        $data = $matches[1];
                        $hora = $matches[2];
                        $conteudo = $matches[3];

                        echo $bold . $amarelo . "[!] " . $data . " " . $hora . " " . $branco . "$conteudo" . "\n";
                    }
                }
            } else {
                echo $bold . "\e[31m[!] Nenhum dado encontrado.\n";
            }

            echo "\n";

            echo $bold . $azul . "[+] Checando se o replay foi passado...\n";

                $comandoArquivos = 'adb shell "ls -t /sdcard/Android/data/com.dts.freefireth/files/MReplays/*.bin 2>/dev/null"';
                $output = shell_exec($comandoArquivos);
                $arquivos = $output !== null ? array_filter(explode("\n", trim($output))) : [];
                
                $motivos = [];
                $arquivoMaisRecente = null;
                $ultimoModifyTime = null;
                $ultimoChangeTime = null;
                
                if (empty($arquivos)) {
                    $motivos[] = "Motivo 10 - Nenhum arquivo .bin encontrado na pasta MReplays";
                }
                
                foreach ($arquivos as $indice => $arquivo) {
                    $resultadoStat = shell_exec('adb shell "stat ' . escapeshellarg($arquivo) . '"');
                
                    if ($resultadoStat !== null &&
                        preg_match('/Access: (.*?)\n/', $resultadoStat, $matchAccess) &&
                        preg_match('/Modify: (.*?)\n/', $resultadoStat, $matchModify) &&
                        preg_match('/Change: (.*?)\n/', $resultadoStat, $matchChange)
                    ) {
                        $dataAccess = trim(preg_replace('/ -\d{4}$/', '', $matchAccess[1]));
                        $dataModify = trim(preg_replace('/ -\d{4}$/', '', $matchModify[1]));
                        $dataChange = trim(preg_replace('/ -\d{4}$/', '', $matchChange[1]));
                
                        $accessTime = strtotime($dataAccess);
                        $modifyTime = strtotime($dataModify);
                        $changeTime = strtotime($dataChange);
                
                        if ($indice === 0) {
                            $ultimoModifyTime = $modifyTime;
                            $ultimoChangeTime = $changeTime;
                        }
                
                        if ($accessTime > $modifyTime) {
                            $motivos[] = "Motivo 1 - Access posterior ao Modify " . basename($arquivo);
                        }
                
                        if (
                            preg_match('/\.0+$/', $dataAccess) ||
                            preg_match('/\.0+$/', $dataModify) ||
                            preg_match('/\.0+$/', $dataChange)
                        ) {
                            $motivos[] = "Motivo 2 - Timestamps com .000 " . basename($arquivo);
                        }
                
                        if ($dataModify !== $dataChange) {
                            $motivos[] = "Motivo 3 - Modify diferente de Change no arquivo " . basename($arquivo);
                        }
                
                        if ($indice === 0) {
                            $arquivoMaisRecente = $arquivo;
                        
                            if (preg_match('/(\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2})/', basename($arquivo), $match)) {
                                $nomeNormalizado = preg_replace(
                                    '/^(\d{4})-(\d{2})-(\d{2})-(\d{2})-(\d{2})-(\d{2})$/',
                                    '$1-$2-$3 $4:$5:$6',
                                    $match[1]
                                );
                                $nomeTimestamp = strtotime($nomeNormalizado);
                        
                                preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\.(\d+)/', $dataModify, $modifyParts);
                                $dataModifyBase = $modifyParts[1] ?? '';
                                $nanosModify = (int)($modifyParts[2] ?? 0);
                                $modifyTimestamp = strtotime($dataModifyBase);
                        
                                if ($nomeTimestamp !== false && $modifyTimestamp !== false) {
  
                                    $nomeNsTotal = $nomeTimestamp * 1_000_000_000;
                                    $modifyNsTotal = ($modifyTimestamp * 1_000_000_000) + $nanosModify;
                        
                                    $diffNs = abs($modifyNsTotal - $nomeNsTotal);
                        
                                    if ($diffNs > 1_000_000_000) { 
                                        $motivos[] = "Motivo 4 - Nome do arquivo não bate com Modify: " . basename($arquivo);
                                    }
                                } else {
                                    $motivos[] = "Motivo 4 - erro ao converter timestamps (Modify: $dataModify, Nome: {$match[1]})";
                                }
                            }
                        }
                        
                        $jsonPath = preg_replace('/\.bin$/', '.json', $arquivo);
                        $jsonStat = shell_exec('adb shell "stat ' . escapeshellarg($jsonPath) . ' 2>/dev/null"');
                        if ($jsonStat && preg_match('/Access: (.*?)\n/', $jsonStat, $matchJsonAccess)) {
                            $jsonAccess = trim(preg_replace('/ -\d{4}$/', '', $matchJsonAccess[1]));
                            $dataBinTimes = [$dataAccess, $dataModify, $dataChange];
                            if (!in_array($jsonAccess, $dataBinTimes)) {
                                $motivos[] = "Motivo 8 - Access do .json diferente dos tempos do .bin" . basename($jsonPath);
                            }
                        }
                        if (!$jsonStat) {
                            $motivos[] = "Motivo 8 - Arquivo JSON ausente: " . basename($jsonPath);
                        }

                    }
                }
                
                $resultadoPasta = shell_exec('adb shell "stat /sdcard/Android/data/com.dts.freefireth/files/MReplays 2>/dev/null"');
                if ($resultadoPasta) {
                    preg_match_all('/^(Access|Modify|Change):\s(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}\.\d+)(?:\s[+-]\d{4})?/m', $resultadoPasta, $matches, PREG_SET_ORDER);
                    $timestamps = [];
                    foreach ($matches as $match) {
                        $timestamps[$match[1]] = trim($match[2]);
                    }
                
                    if (count($timestamps) === 3) {
                        $pastaModifyTime = strtotime($timestamps['Modify']);
                        $pastaChangeTime = strtotime($timestamps['Change']);
                
                        if ($ultimoModifyTime && $pastaModifyTime > $ultimoModifyTime) {
                            $motivos[] = "Motivo 7 - Pasta modificada após o último replay";
                        }
                        if ($ultimoChangeTime && $pastaChangeTime > $ultimoChangeTime) {
                            $motivos[] = "Motivo 7 - Pasta modificada após o último replay";
                        }
                
                        if ($timestamps['Access'] === $timestamps['Modify'] && $timestamps['Modify'] === $timestamps['Change']) {
                            $motivos[] = "Motivo 5 - Access, Modify e Change idênticos";
                        }
                
                        if (preg_match('/\.0+$/', $timestamps['Modify']) || preg_match('/\.0+$/', $timestamps['Change'])) {
                            $motivos[] = "Motivo 6 - Milissegundos .000 na pasta";
                        }
                
                        if ($timestamps['Modify'] !== $timestamps['Change']) {
                            $motivos[] = "Motivo 11 - Modify diferente de Change na pasta";
                        }

                        if (
                            $arquivoMaisRecente &&
                            isset($timestamps['Change'])
                        ) {
                            $changeMReplays = trim($timestamps['Change']);
                        
                            $statBin = shell_exec('adb shell "stat ' . escapeshellarg($arquivoMaisRecente) . ' 2>/dev/null"');
                            preg_match_all('/Access: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)(?: [-+]\d{4})?/', $statBin, $matchesBin);
                            $binAccess = isset($matchesBin[1]) ? end($matchesBin[1]) : '';
                        
                            $jsonPath = preg_replace('/\.bin$/', '.json', $arquivoMaisRecente);
                            $statJson = shell_exec('adb shell "stat ' . escapeshellarg($jsonPath) . ' 2>/dev/null"');
                            preg_match_all('/Access: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)(?: [-+]\d{4})?/', $statJson, $matchesJson);
                            $jsonAccess = isset($matchesJson[1]) ? end($matchesJson[1]) : '';
                        
                            if ($binAccess !== $changeMReplays && $jsonAccess !== $changeMReplays) {
                                $motivos[] = "Motivo 12 - Change da pasta MReplays não bate com Access do .bin ou .json\n" .
                                            "Change MReplays: $changeMReplays\n" .
                                            "Access .bin:     $binAccess\n" .
                                            "Access .json:    $jsonAccess";
                            }
                        }
                
                        if ($arquivoMaisRecente && isset($timestamps['Access'])) {
                            if (preg_match('/(\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2})/', basename($arquivoMaisRecente), $match)) {
                                $nomeNormalizado = str_replace('-', '', $match[1]);
                                $modifyPastaNormalizado = str_replace(['-', ' ', ':'], '', $timestamps['Modify']);
                                if (preg_match('/\.(\d{2})(\d+)/', $timestamps['Access'], $milisegundosMatch)) {
                                    $doisPrimeiros = (int)$milisegundosMatch[1];
                                    $restante = $milisegundosMatch[2];
                                    $todosZeros = preg_match('/^0+$/', $milisegundosMatch[0]);
                                    $condicaoValida = ($doisPrimeiros <= 90 && preg_match('/^0+$/', $restante));
                                    if (($todosZeros || $condicaoValida) && $nomeNormalizado !== $modifyPastaNormalizado) {
                                        $motivos[] = "Motivo 9 - Nome não bate com Modify da pasta + milissegundos suspeitos" . basename($arquivoMaisRecente);
                                    }
                                }
                            }
                        }
                    }
                }
                
                $comandoLs = 'adb shell "ls -l /sdcard/Android/data/com.dts.freefireth/files/MReplays/*.bin 2>/dev/null"';
                $outputLs = shell_exec($comandoLs);
                $linhasLs = $outputLs !== null ? array_filter(explode("\n", trim($outputLs))) : [];
                
                foreach ($linhasLs as $linha) {
                    if (preg_match('/^-[rwx-]{9}\s+\d+\s+(\S+)\s+(\S+)\s+\d+\s+[\d-]+\s+[\d:]+\s+(.+\.bin)$/', $linha, $matches)) {
                        $dono = $matches[1];
                        $grupo = $matches[2];
                        $nomeArquivo = basename($matches[3]);
                        
                        if ($dono === $grupo) {
                            $motivos[] = "Motivo 13 - Dono e grupo iguais (suspeito): $nomeArquivo (dono: $dono, grupo: $grupo)";
                        }
                    }
                }

                if (!empty($motivos)) {
                    echo $bold . $vermelho . "[!] Passador de replay detectado, aplique o W.O!\n";
                    foreach (array_unique($motivos) as $motivo) {
                        echo "    - " . $motivo . "\n";
                    }
                } else {
                    echo $bold . $fverde . "[i] Nenhum replay foi passado e a pasta MReplays está normal.\n";
                }

                if (!empty($resultadoPasta)) {
                    preg_match('/Access: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)/', $resultadoPasta, $matchAccessPasta);
                    
                    if (!empty($matchAccessPasta[1])) {
                        $dataAccessPasta = trim($matchAccessPasta[1]);
                        $dataAccessPastaSemMilesimos = preg_replace('/\.\d+.*$/', '', $dataAccessPasta);
                        
                        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dataAccessPastaSemMilesimos);
                        $dataFormatada = $dateTime ? $dateTime->format('d-m-Y H:i:s') : $dataAccessPastaSemMilesimos;

                        $cmd = "adb shell dumpsys package com.dts.freefireth | grep -i firstInstallTime";
                        $firstInstallTime = shell_exec($cmd);

                        if ($firstInstallTime !== null && preg_match('/firstInstallTime=([\d-]+ \d{2}:\d{2}:\d{2})/', $firstInstallTime, $matches)) {
                            $dataInstalacao = trim($matches[1]);
                            $dateTimeInstalacao = DateTime::createFromFormat('Y-m-d H:i:s', $dataInstalacao);
                            $dataInstalacaoFormatada = $dateTimeInstalacao ? $dateTimeInstalacao->format('d-m-Y H:i:s') : "Formato inválido";
                        } else {
                            $dataInstalacaoFormatada = "Não encontrada";
                        }

                        echo $bold . $amarelo . "[+] Data de acesso da pasta MReplays: $dataFormatada\n";
                        echo $bold . $amarelo . "[*] Data de instalação do Free Fire: $dataInstalacaoFormatada\n";
                        echo $bold . $branco . "[#] Verifique a data de instalação do jogo com a data de acesso da pasta MReplays para ver se o jogo foi recém instalado antes da partida, se não, vá no histórico e veja se o player jogou outras partidas recentemente, se sim, aplique o W.O!\n\n";
                    } else {
                        echo $bold . $vermelho . "[!] Não foi possível obter a data de acesso da pasta MReplays\n\n";
                    }
                }

                echo $bold . $azul . "[+] Checando bypass de Wallhack/Holograma...\n";

                $pastasParaVerificar = [
                    "/sdcard/Android/data/com.dts.freefireth/files/contentcache/Optional/android/gameassetbundles",
                    "/sdcard/Android/data/com.dts.freefireth/files/contentcache/Optional/android",
                    "/sdcard/Android/data/com.dts.freefireth/files/contentcache/Optional",
                    "/sdcard/Android/data/com.dts.freefireth/files/contentcache",
                    "/sdcard/Android/data/com.dts.freefireth/files",
                    "/sdcard/Android/data/com.dts.freefireth",
                    "/sdcard/Android/data",
                    "/sdcard/Android"
                ];

                foreach ($pastasParaVerificar as $pasta) {
                    $comandoStat = 'adb shell stat ' . escapeshellarg($pasta) . ' 2>&1';
                    $resultadoStat = shell_exec($comandoStat);
                
                    if ($resultadoStat !== null && strpos($resultadoStat, 'File:') !== false) {
                        preg_match('/Modify: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)/', $resultadoStat, $matchModify);
                        preg_match('/Change: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)/', $resultadoStat, $matchChange);
                
                        if ($matchModify && $matchChange) {
                            $dataModify = trim($matchModify[1]);
                            $dataChange = trim($matchChange[1]);
                
                            $dataModifyFormatada = preg_replace('/\.\d+.*$/', '', $dataModify);
                            $dataChangeFormatada = preg_replace('/\.\d+.*$/', '', $dataChange);
                
                            if ($dataModifyFormatada !== $dataChangeFormatada) {
                                $nomefinalpasta = basename($pasta);
                                
                                $dateTimeChange = DateTime::createFromFormat('Y-m-d H:i:s', $dataChangeFormatada);
                                $dataChangeFormatadaLegivel = $dateTimeChange ? $dateTimeChange->format('d-m-Y H:i:s') : $dataChangeFormatada;
                                
                                echo $bold . $vermelho . "[!] Bypass de renomear/substituir na pasta: $nomefinalpasta! Confira se o horário é após a partida, se sim, aplique o W.O!\n";
                                echo $bold . $amarelo . "[i] Horário do renomeio/substituição: $dataChangeFormatadaLegivel\n\n";
                            }
                        }
                    }
                }

                $comandoFindBin = 'adb shell ls -t "/sdcard/Android/data/com.dts.freefireth/files/MReplays" | grep "\.bin$" | head -n 1';
                $arquivoBinMaisRecente = shell_exec($comandoFindBin);

                if ($arquivoBinMaisRecente !== null && $arquivoBinMaisRecente !== '') {
                    $arquivoBinMaisRecente = trim($arquivoBinMaisRecente);
                    $caminhoCompletoBin = "/sdcard/Android/data/com.dts.freefireth/files/MReplays/$arquivoBinMaisRecente";
                    $comandoStatBin = 'adb shell stat ' . escapeshellarg($caminhoCompletoBin) . ' 2>&1';
                    $resultadoStatBin = shell_exec($comandoStatBin);
                    if ($resultadoStatBin !== null) {
                        preg_match('/Access: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStatBin, $matchAccessBin);

                        if ($matchAccessBin) {
                            $dataAccessBin = $matchAccessBin[1];
                            $timestampAccessBinOriginal = strtotime($dataAccessBin);
                            $timestampAccessBinComMargem = $timestampAccessBinOriginal - (10 * 60);

                            $pastasParaVerificar = [
                                "/sdcard/Android/data/com.dts.freefireth/files/contentcache",
                                "/sdcard/Android/data/com.dts.freefireth/files/contentcache/Optional/android"
                            ];

                            $bypassDetectado = false;
                            foreach ($pastasParaVerificar as $pasta) {
                                $comandoStat = 'adb shell stat ' . escapeshellarg($pasta) . ' 2>&1';
                                $resultadoStat = shell_exec($comandoStat);

                                if ($resultadoStat !== null) {
                                    preg_match('/Access: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStat, $matchAccess);
                                    preg_match('/Modify: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStat, $matchModify);
                                    preg_match('/Change: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStat, $matchChange);

                                    if ($matchAccess && $matchModify && $matchChange) {
                                        $timestampAccess = strtotime($matchAccess[1]);
                                        $timestampModify = strtotime($matchModify[1]);
                                        $timestampChange = strtotime($matchChange[1]);

                                        if (
                                            $timestampAccess > $timestampAccessBinComMargem ||
                                            $timestampModify > $timestampAccessBinComMargem ||
                                            $timestampChange > $timestampAccessBinComMargem
                                        ) {
                                            $bypassDetectado = true;
                                            break;
                                        }
                                    }
                                }
                            }

                            if ($bypassDetectado) {
                                echo $bold . $vermelho . "[!] Modificando pastas após o fim da partida, aplique o W.O!\n\n";
                            } else {
                                echo $bold . $verde . "[+] Nenhum bypass de holograma detectado.\n\n";
                            }
                        } else {
                            echo $bold . $amarelo . "[!] Não foi possível obter a data do último .bin.\n";
                        }
                    }
                } else {
                    echo $bold . $vermelho . "[!] Nenhum .bin encontrado em MReplays.\n";
                }

                $cmd = "adb shell dumpsys package com.dts.freefireth | grep -i firstInstallTime";
                $firstInstallTime = shell_exec($cmd);

                $firstInstallDate = null;
                if ($firstInstallTime !== null && preg_match('/firstInstallTime=(\d{4}-\d{2}-\d{2})/', $firstInstallTime, $matchInstall)) {
                    $firstInstallDate = $matchInstall[1];
                }

                $cmdUpdate = "adb shell dumpsys package com.dts.freefireth | grep -i lastUpdateTime";
                $lastUpdateTime = shell_exec($cmdUpdate);

                $lastUpdateDate = null;
                if ($lastUpdateTime !== null && preg_match('/lastUpdateTime=(\d{4}-\d{2}-\d{2})/', $lastUpdateTime, $matchUpdate)) {
                    $lastUpdateDate = $matchUpdate[1];
                }

                $pastaShaders = "/sdcard/Android/data/com.dts.freefireth/files/contentcache/Optional/android/gameassetbundles";

                $comandoFind = 'adb shell find ' . escapeshellarg($pastaShaders) . ' -name "shaders*" -type f 2>&1';
                $arquivosShaders = shell_exec($comandoFind);
                
                if ($arquivosShaders !== null && !empty($arquivosShaders)) {
                    $arquivosShaders = explode("\n", trim($arquivosShaders));
                
                    foreach ($arquivosShaders as $arquivo) {
                        if (empty($arquivo)) continue;
                
                        $comandoStat = 'adb shell stat ' . escapeshellarg($arquivo) . ' 2>&1';
                        $resultadoStat = shell_exec($comandoStat);
                
                        if ($resultadoStat !== null && strpos($resultadoStat, 'File:') !== false) {
                            preg_match('/Access: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStat, $matchAccess);
                            preg_match('/Modify: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStat, $matchModify);
                            preg_match('/Change: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $resultadoStat, $matchChange);
                
                            if ($matchAccess && $matchModify && $matchChange) {
                                $accessDate = $matchAccess[1];
                                $modifyDate = $matchModify[1];
                                $changeDate = $matchChange[1];
                
                                $nomeArquivo = basename($arquivo);
                
                                if ($accessDate === $modifyDate && $modifyDate === $changeDate) {
                                    $timestampArquivo = strtotime($accessDate);
                                    $ignorarAviso = false;
                                    
                                    if ($firstInstallDate) {
                                        $timestampInstalacao = strtotime($firstInstallDate);
                                        $diferencaSegundosInstall = abs($timestampArquivo - $timestampInstalacao);
                                        
                                        if ($diferencaSegundosInstall <= 86400) {
                                            $ignorarAviso = true;
                                        }
                                    }

                                    if (!$ignorarAviso && $lastUpdateDate) {
                                        $timestampAtualizacao = strtotime($lastUpdateDate);
                                        $diferencaSegundosUpdate = abs($timestampArquivo - $timestampAtualizacao);
                                        
                                        if ($diferencaSegundosUpdate <= 86400) {
                                            $ignorarAviso = true;
                                        }
                                    }
                                    
                                    if ($ignorarAviso) {
                                        continue;
                                    }
                                
                                    echo $bold . $laranja . "[!] Possível Bypass Holograma detectado (ACCESS, MODIFY, CHANGE iguais)\n";
                                    echo $bold . $laranja . "[!] Arquivo: $nomeArquivo\n";
                
                                    $dateTimeAccess = DateTime::createFromFormat('Y-m-d H:i:s', $accessDate);
                                    $dataAccessFormatada = $dateTimeAccess ? $dateTimeAccess->format('d-m-Y H:i:s') : $accessDate;
                
                                    $dateTimeInstall = DateTime::createFromFormat('Y-m-d H:i:s', $firstInstallDate);
                                    $dataInstallFormatada = $dateTimeInstall ? $dateTimeInstall->format('d-m-Y H:i:s') : $firstInstallDate;
                
                                    echo $bold . $laranja . "[!] Data da modificação (Access/Modify/Change): $dataAccessFormatada\n";
                                    echo $bold . $laranja . "[!] Data de instalação do FF: $dataInstallFormatada\n";
                                    
                                    if ($lastUpdateDate) {
                                        $dateTimeUpdate = DateTime::createFromFormat('Y-m-d H:i:s', $lastUpdateDate);
                                        $dataUpdateFormatada = $dateTimeUpdate ? $dateTimeUpdate->format('d-m-Y H:i:s') : $lastUpdateDate;
                                        echo $bold . $laranja . "[!] Data de atualização do FF: $dataUpdateFormatada\n";
                                    }
                                    
                                    echo $bold . $laranja . "[!] Se for após a partida, aplique o W.O!\n\n";
                                    continue;
                                }
                
                                if ($modifyDate !== $changeDate) {
                                    $dateTimeChange = DateTime::createFromFormat('Y-m-d H:i:s', $changeDate);
                                    $dataChangeFormatadaLegivel = $dateTimeChange ? $dateTimeChange->format('d-m-Y H:i:s') : $changeDate;
                
                                    echo $bold . $vermelho . "[!] Arquivo shader modificado: $nomeArquivo\n";
                                    echo $bold . $amarelo . "[i] Horário da modificação: $dataChangeFormatadaLegivel\n";
                                    echo $bold . $vermelho . "[!] Verifique se a data é após a partida, se sim aplique o W.O!\n\n";
                                }
                            }
                        }
                    }
                } else {
                    echo $bold . $amarelo . "[i] Nenhum arquivo de shader encontrado.\n";
                }

                echo $bold . $branco . "\n\n\t Obrigado por compactuar por um cenário limpo de cheats.\n";
                echo $bold . $branco . "\t                 Com carinho, AucerolaSS & KellerSS & SheikSS...\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";

        } elseif ($opcaoscanner == "2") {

            system("clear");
            keller_banner();

            if (!shell_exec("adb version > /dev/null 2>&1"))
            {
                system("pkg install -y android-tools > /dev/null 2>&1");
            }

            date_default_timezone_set('America/Sao_Paulo');
            shell_exec('adb start-server > /dev/null 2>&1');

            $comandoDispositivos = shell_exec("adb devices 2>&1");

                if ($comandoDispositivos === null || empty($comandoDispositivos) || strpos($comandoDispositivos, "device") === false || strpos($comandoDispositivos, "no devices") !== false) {
                    echo "\033[1;31m[!] Nenhum dispositivo encontrado. Faça o pareamento de IP ou conecte um dispositivo via USB.\n\n";
                    exit;
                }

                $comandoVerificarFF = shell_exec("adb shell pm list packages --user 0 | grep com.dts.freefiremax 2>&1");

                if ($comandoVerificarFF !== null && !empty($comandoVerificarFF) && strpos($comandoVerificarFF, "more than one device/emulator") !== false) {
                    echo $bold . $vermelho . "[!] Pareamento realizado de maneira incorreta, digite \"adb disconnect\" e refaça o processo.\n\n";
                    exit;
                }
                
                if ($comandoVerificarFF !== null && !empty($comandoVerificarFF) && strpos($comandoVerificarFF, "com.dts.freefiremax") !== false) {
                } else {
                    echo $bold . $vermelho . "[!] O FreeFire MAX está desinstalado, cancelando a telagem...\n\n";
                    exit;
                }

                $comandoVersaoAndroid = "adb shell getprop ro.build.version.release";
                $resultadoVersaoAndroid = shell_exec($comandoVersaoAndroid);

                if ($resultadoVersaoAndroid !== null && !empty($resultadoVersaoAndroid)) {
                    echo $bold . $azul . "[+] Versão do Android: " . trim($resultadoVersaoAndroid) . "\n";
                } else {
                    echo $bold . $vermelho . "[!] Não foi possível obter a versão do Android.\n";
                }

                $comandoSu = 'su 2>&1';
                $resultadoSu = shell_exec($comandoSu);

                echo $bold . $azul . "[+] Checando se possui Root (se o programa travar, root detectado)...\n";
                if ($resultadoSu !== null && !empty($resultadoSu) && strpos($resultadoSu, 'No su program found') !== false) {
                    echo $bold . $fverde . "[-] O dispositivo não tem root.\n\n";
                } else {
                    echo $bold . $vermelho . "[+] Root detectado no dispositivo Android.\n\n";
                }
                
            echo $bold . $azul . "[+] Verificando scripts ativos em segundo plano...\n";
            $comandoScripts = 'adb shell "pgrep -a bash | awk \'{\$1=\"\"; sub(/^ /,\"\"); print}\' | grep -vFx \"/data/data/com.termux/files/usr/bin/bash -l\""';
            $scriptsAtivos = shell_exec($comandoScripts);
            
            if ($scriptsAtivos !== null && trim($scriptsAtivos) !== '') {
                echo $bold . $vermelho . "[!] Scripts detectados rodando em segundo plano! Cancelando scanner...\n";
                echo $bold . $amarelo . "Scripts encontrados:\n" . trim($scriptsAtivos) . "\n\n";
                exit;
            }
            
            echo $bold . $fverde . "[i] Nenhum script ativo detectado.\n";
            echo $bold . $azul . "[+] Finalizando sessões bash desnecessárias...\n";
            $comandoKillBash = 'adb shell "current_pid=\$\$; for pid in \$(pgrep bash); do [ \"\$pid\" -ne \"\$current_pid\" ] && kill -9 \$pid; done"';
            shell_exec($comandoKillBash);
            echo $bold . $fverde . "[i] Sessões desnecessárias finalizadas.\n\n";

            echo $bold . $azul . "[+] Verificando bypasses de funções shell...\n";
            detectarBypassShell();

            echo $bold . $azul . "[+] Verificando configurações de acesso ao uso...\n";
            verificarAcessoUso();

            echo $bold . $azul . "[+] Verificando gerenciadores de arquivos...\n";
            verificarGerenciadoresArquivos();

            echo $bold . $azul . "[+] Verificando permissões suspeitas...\n";
            verificarPermissoesSuspeitas();

            echo $bold . $azul . "[+] Verificando módulos Magisk...\n";
            verificarModulosMagisk();

            echo $bold . $azul . "[+] Verificando apps de cheat...\n";
            verificarAppsSuspeitos();

            echo $bold . $azul . "[+] Verificando processos suspeitos...\n";
            verificarProcessosSuspeitos();

            echo $bold . $azul . "[+] Verificando arquivos de cheat...\n";
            verificarArquivosSuspeitos();

            echo $bold . $azul . "[+] Verificando configurações de desenvolvedor...\n";
            verificarConfiguracoesDesenvolvedor();

            echo $bold . $azul . "[+] Verificando tweaks de performance...\n";
            verificarTweaksPerformance();

            echo $bold . $azul . "[+] Verificando virtualização...\n";
            verificarVirtualizacao();

            echo $bold . $azul . "[+] Checando se o dispositivo foi reiniciado recentemente...\n";
            $comandoUPTIME = shell_exec("adb shell uptime");

            if ($comandoUPTIME !== null && preg_match('/up (\d+) min/', $comandoUPTIME, $filtros)) {
                $minutos = $filtros[1];
                echo $bold . $vermelho . "[!] O dispositivo foi iniciado recentemente (há $minutos minutos).\n\n";
            } else {
                echo $bold . $fverde . "[i] Dispositivo não reiniciado recentemente.\n\n";
            }

            $logcatTime = shell_exec("adb logcat -d -v time | head -n 2");
            if ($logcatTime !== null) {
                preg_match('/(\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $logcatTime, $matchTime);

                if (!empty($matchTime[1])) {

                    $date = DateTime::createFromFormat('m-d H:i:s', $matchTime[1]);
                    $formattedDate = $date->format('d-m H:i:s'); 

                    echo $bold . $amarelo . "[+] Primeira log do sistema: " . $formattedDate . "\n";
                    echo $bold . $branco . "[+] Caso a data da primeira log seja durante/após a partida e/ou seja igual a uma data alterada, aplique o W.O!\n\n";

                } else {
                    echo $bold . $vermelho . "[!] Não foi possível capturar a data/hora do sistema.\n\n";
                }
            }

            echo $bold . $branco . "\n\n\t Obrigado por compactuar por um cenário limpo de cheats.\n";
            echo $bold . $branco . "\t                 Com carinho, AucerolaSS & KellerSS & SheikSS...\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";

        } elseif ($opcaoscanner == 's' || $opcaoscanner == 'S') {
            echo "\n\n\t Obrigado por compactuar por um cenário limpo de cheats.\n\n";
            die();
        }
      }
?>
