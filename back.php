<?php
    session_start();
    $identify = (str_repeat(0,3-strlen($_SESSION['user_mtworld']))).$_SESSION['user_mtworld'];

    function somaAcum($valor,$qtd){
        return $valor*$qtd;
    }
    function updateDetail(){
        $identify = (str_repeat(0,3-strlen($_SESSION['user_mtworld']))).$_SESSION['user_mtworld'];
        $path="listas/";
        $diretorio = dir($path);
        $content = "";
        while($arquivo = $diretorio->read()){
            if(substr($arquivo,0,3)==$identify&&substr($arquivo,3)!='_start.js'&&substr($arquivo,3)!='_detail.php'){
                $dados = implode('',file('listas/'.$arquivo));
                if(strpos($dados,'[')>-1){
                    $dados = substr($dados,strpos($dados,'['));
                    $dados = substr($dados,0,strpos($dados,'var colorPage'));
                    $dados = str_replace('[',' array(',$dados);
                    $dados = str_replace(']',')',$dados);
                    $dados = "\$dados = ".$dados; 
                    eval($dados);
                    $qtd = array_sum(array_column($dados,1));
                    $total = array_sum(array_map('somaAcum',array_column($dados,3),array_column($dados,1)));
                }else{
                    $qtd = 0;
                    $total = 0;
                }
                $content .= "<li class='list-group-item'>";
                $content .= "<span class='linkLista'>";
                $content .= "<a onclick='window.onbeforeunload=true;' href='back.php?list=".$arquivo."'>".$arquivo."</a>";
                $content .= "</span>";
                $content .= "<span class='material-icons text-danger float-right align-middle rounded mx-2' ";
                $content .= "onclick=\"confirmacao('excluir','".$arquivo."');\">delete</span>";
                $content .= "<span class='badge badge-dark mx-2 p-2 float-right'><b>Itens:</b> ".(strlen($qtd)==1?'0'.$qtd:$qtd)."</span>";
                $content .= "<span class='badge badge-info mx-2 p-2 float-right'><b>Total:</b> R$ ".$total."</span>";
                $content .= "</li>";
            }
        }
        $arquivo = fopen('listas/'.$identify.'_detail.php','w');
        if ($arquivo == false) return false;
        else{
            fwrite($arquivo,$content);
            fclose($arquivo);
            return true;
        }
    }

    if(isset($_POST['bdjs'])){
        $arquivo = fopen('listas/'.$identify.'_start.js','w');
        if ($arquivo == false) header('Location: index.php?erro=1');
        else{
            fwrite($arquivo,$_POST['bdjs']);
            fclose($arquivo);
            if(isset($_GET['nameLista'])){
                $arquivo = fopen("listas/".$identify."_{$_GET['nameLista']}.js",'w');
                if ($arquivo == false) header('Location: index.php?erro=1');
                else{
                    fwrite($arquivo,$_POST['bdjs']);
                    fclose($arquivo);
                    if(updateDetail()) header('Location: index.php');
                    else header('Location: index.php?erro=6');
                }
            }
            else header('Location: index.php');
        }
    }else
    if(isset($_POST['arqjs'])&&isset($_POST['arqname'])){                    
        $arquivo = fopen('listas/'.$identify.'_start.js','w');
        if ($arquivo == false) header('Location: index.php?erro=1');
        else{
            fwrite($arquivo,$_POST['arqjs']);
            fclose($arquivo);

            $arquivo = fopen("listas/{$_POST['arqname']}.js",'w');
            if ($arquivo == false) header('Location: index.php?erro=2');
            else{
                fwrite($arquivo,$_POST['arqjs']);
                fclose($arquivo);
                if(updateDetail()) header('Location: index.php');
                else header('Location: index.php?erro=6');
            }
        }
    }else
    if(isset($_GET['close'])){        
        $color = $_GET['close'];
        if(isset($_GET['clean'])&&$_GET['clean']=='true'){
            $_GET['close'] = "var arrLista = []; var colorPage = ".$color.";";
        }else{
            $_GET['close'] = implode('',file('listas/'.$identify.'_start.js'));
            $_GET['close'] = substr($_GET['close'],0,strpos($_GET['close'],'var colorPage')).' var colorPage = '.$color.';';
        }
        $arquivo = fopen('listas/'.$identify.'_start.js','w');
        if ($arquivo == false) header('Location: index.php?erro=3');
        else{
            fwrite($arquivo,$_GET['close']);
            fclose($arquivo);
            header('Location: index.php');
        }
    }else
    if(isset($_GET['list'])){
        $_GET['list'] = implode('',file('listas/'.$_GET['list']));
        $arquivo = fopen('listas/'.$identify.'_start.js','w');
        if ($arquivo == false) header('Location: index.php?erro=4');
        else{
            fwrite($arquivo,$_GET['list']);
            fclose($arquivo);
            header('Location: index.php');
        }
    }else
    if(isset($_GET['delete'])){
        if($_GET['delete']!=''){
            if(unlink("listas/".$_GET['delete'])){
                if(updateDetail()) header('Location: index.php?glist&succ=1');
                else header('Location: index.php?erro=6');
            }
            else header('Location: index.php?glist&erro=5');
        }else header('Location: index.php?glist&erro=5');
    }else
    if(isset($_GET['update'])){
        if(updateDetail()) header('Location: index.php?glist');
        else header('Location: index.php?erro=6');
    }
?> 