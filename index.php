<?php
  session_start();
  include('../conn/function.php');
  if(!(isset($_SESSION['user_mtworld'])&&$_SESSION['user_mtworld']>0)){
    if(isset($_COOKIE['mtworldPass'])&&isset($_COOKIE['mtworldKey'])){
      $sql="select * from usuario where email='{$_COOKIE['mtworldPass']}' and senha='{$_COOKIE['mtworldKey']}';";
      if($linha = (enviarComand($sql,'bd_mtworld'))->fetch_assoc()){
        $_SESSION['user_mtworld'] = $linha['id'];
        $_SESSION['user_mtworld_nome'] = $linha['nome'];
        $_SESSION['user_mtworld_email'] = $linha['email'];
      } 
    }
    else header('Location: ../index.php?msg=5');
  }
  $identify = (str_repeat(0,3-strlen($_SESSION['user_mtworld']))).$_SESSION['user_mtworld'];
  $file = 'listas/'.$identify.'_detail.php';
  if(!file_exists($file)){
    header('Location: back.php?update');
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Matth Mart</title>
  <!-- Icone -->
  <link rel="icon" href="../img/arrow-icon.jpg" type="image">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- Bootstrap core CSS -->
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,500&display=swap" rel="stylesheet">
  <style>
      ::-webkit-scrollbar { width: 4px; height: 4px; background: #222; } /* width */
      ::-webkit-scrollbar-track { background: #222; border-radius: 5px; } /* Track */
      ::-webkit-scrollbar-thumb { background: #666; border-radius: 5px; } /* Handle */
      ::-webkit-scrollbar-thumb:hover { background: #555; } /* Handle on hover */
  </style>
  <style>
      .font-custom{ font-family: 'Playfair Display', serif; }
      h1{ font-family: 'Playfair Display', serif; }
      [onclick]{ cursor: pointer; }
      #paiTableLista .toggle-view{ display: none; }
      @media (max-width: 700px){       
        #paiTableLista td:nth-child(2),#paiTableLista th:nth-child(2),
        #paiTableLista td:nth-child(3),#paiTableLista th:nth-child(3){
          display: none;
        }
        #paiTableLista .toggle-view{
          display: block;
        }
        .linkLista{
          display: block;
          text-align: center;
        }
      }
  </style>
  <script src="../jquery/jquery.js"></script>
  <script>
      var identify = <?php echo $_SESSION['user_mtworld']; ?>;
      
      if(!(typeof(arrLista) != "undefined" && arrLista !== null)) var arrLista = new Array();
      if(!(typeof(colorPage) != "undefined" && colorPage !== null)) var colorPage = false;

      function msg(p){
          $('#modalMsg .modal-body p').html(p);
          $('#modalMsg').modal('show');
      }
      function calculoFooter(){
          somaQ = 0; somaV = 0;
          for(i=0;i<arrLista.length;i++){
              valorS=parseFloat($('#tblValor'+i).val().length>0?$('#tblValor'+i).val():0)*parseFloat($('#tblQtd'+i).val());
              somaQ+=parseFloat($('#tblQtd'+i).val());
              somaV+=valorS;
              $('#s'+i).html("("+valorS.toFixed(2).replace('.',',')+")");
              if(valorS>0){
                if(!$('#s'+i).parent().siblings(':first').children('span').hasClass('text-success')){
                  $('#s'+i).parent().siblings(':first').prepend("<span class='material-icons align-middle text-success mr-1 '>edit_attributes</span>");
                }
              }else{
                if($('#s'+i).parent().siblings(':first').children('span').hasClass('text-success')){
                  $('#s'+i).parent().siblings(':first').children('span.text-success').remove();
                }
              }

          }
          $('tbody th:nth-child(2)').html(somaQ);
          $('tbody th:nth-child(3)').html(""+somaV.toFixed(2).replace('.',','));
      }
      function preencheLista(){
          $('#ulLista').html('<li class="list-group-item bg-dark text-light text-center py-1 font-weight-bold t-dark">Lista</li>');
          $('#tableLista').html('');
          if(arrLista.length>0){
              u="";t="";
              for(i=0;i<arrLista.length;i++){
                u+="<li class='list-group-item' id='li"+arrLista[i][2]+"'><div class='row'>";
                u+="<div class='col'>"+arrLista[i][0]+"</div>";
                u+="<div class='col vinculotblQtd"+i+"'>"+arrLista[i][1]+"</div>";
                u+="<span class='float-right text-danger' onclick='apagar("+arrLista[i][2]+");'>&times;</span>";
                u+="</div></li>";
                //UL ^ TABLE v
                t+="<tr><td class='aloneName' colspan='2'>"+arrLista[i][0]+"</td>";
                t+="<td class='text-right groupValue'>";
                t+="<input type='number' class='border-0 text-right tblQtd rounded' min='1' value='"+arrLista[i][1]+"' id='tblQtd"+i+"' onkeyup='alterar("+arrLista[i][2]+",$(this),1)'>";
                t+="</td><td class='text-right groupValue'><span class='text-muted' id='s"+i+"'>(0,00)</span>";
                t+="<input type='number' class='border-0 text-right tblValor ml-1 rounded' placeholder='0.00' min='0' value='"+(arrLista[i][3]!=0.00?(arrLista[i][3]).toFixed(2).replace(',','.'):'')+"' id='tblValor"+i+"' onkeyup='alterar("+arrLista[i][2]+",$(this),3)'>";
                t+="</td><td class='text-right toggle-view'><span class='material-icons p-2' onclick='toggleView($(this))'>sync_alt</span>";
                t+="</td></tr>";
              }
              $('#ulLista').append(u);
              $('#tableLista').append(t+"<tr class='footer'><th colspan='2'>Total</th><th class='text-right pr-4'>0</th><th class='text-right pr-4'>R$ 0,00</th></tr>");
          }
      }
      function toggleView(elem){
        if(elem.html()=='compare_arrows'){
          if(elem.hasClass('bg-warning')){
            elem.removeClass('bg-warning');
            $('#paiTableLista .groupValue').hide();
            $('#paiTableLista .aloneName').show();
          }else{
            elem.addClass('bg-warning');
            $('#paiTableLista .aloneName').hide();
            $('#paiTableLista .groupValue').show();
          }
        }else{
          if($(elem).parent().siblings('.groupValue').css('display')=='none'){
            $(elem).parent().siblings('.aloneName').hide();
            $(elem).parent().siblings('.groupValue').show();
          }else{
            $(elem).parent().siblings('.groupValue').hide();
            $(elem).parent().siblings('.aloneName').show();
          }
        }
      }
      function offToggler(elem){
        if(elem.children().hasClass('bg-danger')){
          elem.children().removeClass('bg-danger');
          $('#paiTableLista .groupValue').hide();
          $('#paiTableLista .aloneName').show();
          $('#tableLista tr.footer th:first').attr('colspan','1'); 
          $('#paiTableLista .toggle-view').show();
        }else{
          elem.children().addClass('bg-danger');
          $('#paiTableLista td,#paiTableLista th').show();
          $('#tableLista tr.footer th:first').attr('colspan','2'); 
          $('#paiTableLista .toggle-view').hide();
        }
      }
      function contrai(elem){
          if(elem.html()=='keyboard_arrow_up'){
              elem.html('keyboard_arrow_down');
              $('#jumbotron-logo').hide('slow');
              $('.jumbotron').addClass('py-3');
          }else{
              elem.html('keyboard_arrow_up');
              $('#jumbotron-logo').show('slow');
              $('.jumbotron').removeClass('py-3');
          }
      }
      function addList(){
          if($('#nomeItem').val().length>0&&$('#qtdItem').val().length>0){
              i = arrLista.length;
              arrLista[i] = [$('#nomeItem').val(),$('#qtdItem').val(),i,0.00];
              u="<li class='list-group-item"+(!($('#inverteColor').hasClass('text-dark'))?" bg-dark text-light":"")+"'><div class='row'>";
              u+="<div class='col'>"+arrLista[i][0]+"</div>";
              u+="<div class='col'>"+arrLista[i][1]+"</div>";
              u+="</div></li>";
              $('#ulLista').append(u);
              $('#qtdItem').val(1);
              $('#nomeItem').val('').focus();
          }
          else msg('Preencha os Campos');
          console.log(arrLista);
      }
      function saveList(inArquive){
        window.onbeforeunload = true;
        if(arrLista.length>0){
            valor = "var arrLista = [['"+arrLista[0][0]+"',"+arrLista[0][1]+",0,"+arrLista[0][3]+"]";
            for(i=1;i<arrLista.length;i++){
              valor+= ",['"+arrLista[i][0]+"',"+arrLista[i][1]+","+i+","+arrLista[i][3]+"]";
            }
            valor+= "]; var colorPage = ";
            if($('#inverteColor').hasClass('text-dark')) valor+= "false; ";
            else valor+="true; ";
            if(inArquive){
              x = 3-identify.toString().length;
              x = ("0".repeat(x))+identify;
              valor+=" var nameLista = '"+$('#dataLista').val()+"_"+$('#nomeLista').val()+"'; ";
              $('#arqjs').val(valor);
              $('#arqname').val(x+"_"+$('#dataLista').val()+"_"+$('#nomeLista').val());
            }
            else{
              if(typeof(nameLista) != "undefined" && nameLista !== null){
                valor+=" var nameLista = '"+nameLista+"'; ";
                $('#formCadastrar').attr('action','back.php?nameLista='+nameLista);  
              }
              $('#bdjs').val(valor);
            }
            if(inArquive) $('#formSave').submit();
            else $('#formCadastrar').submit();
        }
        else{
          valor = " var colorPage = ";
          if($('#inverteColor').hasClass('text-dark')) valor+= "false; ";
          else valor+="true; ";
          if(inArquive){
            x = 3-identify.toString().length;
            x = ("0".repeat(x))+identify;
            valor+=" var nameLista = '"+$('#dataLista').val()+"_"+$('#nomeLista').val()+"'; ";
            $('#arqjs').val(valor);
            $('#arqname').val(x+"_"+$('#dataLista').val()+"_"+$('#nomeLista').val());
            $('#formSave').submit();
          }
          else{
            if(typeof(nameLista) != "undefined" && nameLista !== null){
              valor+=" var nameLista = '"+nameLista+"'; ";
              $('#formCadastrar').attr('action','back.php?nameLista='+nameLista);
            }
            $('#bdjs').val("var arrLista = new Array();"+valor);
            $('#formCadastrar').submit();
          } 
        } 
      }
      function apagar(id){
          $('#li'+id).remove();
          arrLista.splice(encontra(id),1);
      }
      function alterar(id,elem,pos){
        //1=qtd | 3=valor
        arrLista[encontra(id)][pos] = elem.val();
        $('.vinculo'+elem.attr('id')).html(elem.val());
      }
      function encontra(id){
          retorno = -1;
          for(i=0;i<arrLista.length;i++){
              if(arrLista[i][2]==id){
                  retorno = i;
                  i = arrLista.length;
              }
          }
          return retorno;
      }
      function inverte(p){
          if(p.hasClass('text-dark')){
              p.addClass('text-light').removeClass('text-dark');
              $('#jumbotron-logo').css('background','rgba(250,250,250,.8)').addClass('text-dark');
              $('body,nav,a,.card-body,.modal-body,.modal-header,li,input').addClass('bg-dark text-light');
              $('.non-bg').removeClass('bg-dark');
              $('span').addClass('text-light');
              $('span.text-warning').removeClass('text-light');
              $('table').addClass('table-dark');
              $('.t-dark').removeClass('bg-dark text-light').addClass('text-dark');
              $('.text-danger').removeClass('text-light text-dark');
          }else{
              p.removeClass('text-light').addClass('text-dark');
              $('#jumbotron-logo').css('background','rgba(0,0,0,.4)').removeClass('text-dark');
              $('body,nav,a,.card-body,.modal-body,.modal-header,li,input').removeClass('bg-dark text-light');
              $('span').removeClass('text-light');
              $('table').removeClass('table-dark');
              $('.t-dark').addClass('bg-dark text-light').removeClass('text-dark');
              $('.text-danger').removeClass('text-light text-dark');
          }
          colorPage=!colorPage;
      }
      function formatName(nome){
        data = nome.substr(0,10);
        data = data.split('-');
        data = data.reverse();
        data = data.join('/');
        nome = nome.substr(11) + " " + data; 
        return nome;
      }
      function confirmacao(op,acresc=''){
        switch(op){
          case 'zerar':
            content  = "<button class='btn btn-block btn-sm btn-dark' onclick='arrLista=[]; saveList(false);'>Zerar</button>";
            content += "<button class='btn btn-block btn-sm btn-danger' data-dismiss='modal'>Cancelar</button>";
            $('#modalDinamic .modal-body').html(content);
            $('#modalDinamic').modal('show');
            break;
          case 'fechar':
            content  = "<button class='btn btn-block btn-sm btn-dark' onclick=\"window.onbeforeunload = true; window.location.href='back.php?clean=false&'+(colorPage?'close=true':'close=false');\">Manter Dados</button>";
            content += "<button class='btn btn-block btn-sm btn-danger' onclick=\"window.onbeforeunload = true; window.location.href='back.php?clean=true&'+(colorPage?'close=true':'close=false');\">Limpar Dados</button>";
            $('#modalDinamic .modal-body').html(content);
            $('#modalDinamic').modal('show');
            break;
          case 'excluir':
            content  = "<button class='btn btn-block btn-sm btn-dark' onclick=\"window.onbeforeunload = true; window.location.href='back.php?delete="+acresc+"';\">Excluir</button>";
            content += "<button class='btn btn-block btn-sm btn-danger' data-dismiss='modal'>Cancelar</button>";
            $('#modalDinamic .modal-body').html(content);
            $('#modalDinamic').modal('show');
            break;
        }
      }
      window.onbeforeunload = function(){ return false; };
      $(function(){
          <?php
            if(isset($_GET['erro'])){
              if($_GET['erro']==1) echo " msg('Não foi possível alterar a lista!'); ";
              else if($_GET['erro']==2) echo " msg('Não foi possível salvar a lista!'); ";
              else if($_GET['erro']==3) echo " msg('Houve um erro ao fechar a lista!'); ";
              else if($_GET['erro']==4) echo " msg('Houve um erro ao selecionar a lista!'); ";
              else if($_GET['erro']==5) echo " msg('Houve um erro ao excluir a lista!'); ";
              else if($_GET['erro']==6) echo " msg('Houve um erro ao atualizar os detalhes  das listas!'); ";
            }
            if(isset($_GET['succ'])){
              if($_GET['succ']==1) echo " msg('Lista excluida com sucesso!'); ";
            }
            if(isset($_GET['glist'])) echo " $('#glist').click(); ";
          ?>

          preencheLista();
          calculoFooter();
          
          if(colorPage){ $("#inverteColor").click(); }
          
          $('.tblQtd, .tblValor').attr('onchange','calculoFooter();').attr('style','width: 100px;');
          
          if(typeof(nameLista) != "undefined" && nameLista !== null){
            $('#nomeDaLista').html(" "+formatName(nameLista)).addClass('text-warning');
            $('#saveLista').addClass('d-none');
          }else $('#closeLista').addClass('d-none');
          
          $('.linkLista a').each(function(){ $(this).html(formatName($(this).html().substr(4).replace('.js',''))); });
          
          if($('.toggle-view').css('display')!='none'){
            $('#tableLista tr.footer th:first').attr('colspan','1'); 
            $('#tableLista tr.footer th').show(); 
            $('.linkLista').siblings('span').removeClass('float-right').addClass('d-flex justify-content-center shadow');
            $('.linkLista').siblings('span.text-danger').addClass('border mt-2');
          }
      });
      <?php include('listas/'.$identify.'_start.js'); ?>
    </script>
</head>
<body class="m-0 p-0">
  <!--Header-->
  <div class="jumbotron jumbotron-fluid text-light mb-0 position-relative" style="background: url('img/cozinha.jpg');">
    <div class="container mb-0" style="background: rgba(0,0,0,.4);" id="jumbotron-logo">
      <h1 class="display-4" onclick="window.location.href = '../mmart/';">Matth Mart</h1>
      <p class="lead">Adicione aqui sua lista de Compras para que posso organizá-la da melhor forma.</p>
    </div>
    <div class="position-absolute d-flex justify-content-center" style="bottom: 0px;width: 100vw;">
      <span class="material-icons px-3" onclick="contrai($(this));" style="background: rgba(0,0,0,.4);">keyboard_arrow_up</span>
    </div>
  </div>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#"><span class="material-icons align-middle">shopping_cart</span></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <a class="nav-item nav-link active" href="#" onclick="$('.nav-link').removeClass('active'); $(this).addClass('active'); $('#lGerencia').hide('slow'); $('#lCompra').show('slow');">Lista de Compras<span class="sr-only">(current)</span></a>
        <a class="nav-item nav-link" href="#" onclick="$('#modalCadastrar').modal('show');">Adicionar Item</a>
        <a class="nav-item nav-link" href="#" onclick="$('.nav-link').removeClass('active'); $(this).addClass('active'); $('#lCompra').hide('slow'); $('#lGerencia').show('slow');" id="glist">Gerenciar Lista</a>
        <div class="d-flex justify-content-between border px-2 rounded">
          <a class="nav-item nav-link text-dark rounded" href="#" onclick="inverte($(this));" id="inverteColor"><span class="material-icons align-middle">group_work</span></a>
          <a class="nav-item nav-link text-dark rounded navbar-toggler border-0 mt-1" href="#" onclick="offToggler($(this))"><span class="material-icons align-middle border px-1 rounded">compare_arrows</span></a>
          <?php if(isset($_SESSION['user_mtworld'])&&$_SESSION['user_mtworld']>0){ ?>
            <a class="nav-item nav-link text-dark rounded" style="opacity: .9" id="aMatthNavigate" onclick="$('#matthNavigate').modal('show');" href="#">
              <span class="material-icons align-middle">ac_unit</span>
            </a>
          <?php } ?>
        </div>
      </div>
    </div>
  </nav>
  <!--Body-->
  <div class="container-fluid">
    <!-- Lista de Compras -->
    <div class="card mb-3" id="lCompra">
      <div class="card-header bg-dark text-center text-light font-weight-bold t-dark">
        <span class="material-icons align-middle">shopping_cart</span> 
        Lista<span id="nomeDaLista"> de Compras</span>

        <a href="#" onclick="confirmacao('fechar');" class="t-dark bg-dark text-danger float-right rounded" id="closeLista">
          <i class="material-icons align-middle">close</i>
        </a>
        <a href="#" onclick="$('#modalSave').modal('show');" class="t-dark bg-dark text-light float-right rounded"  id="saveLista">
          <i class="material-icons align-middle">save</i>
        </a>
        <a href="#" onclick="saveList(false);" class="t-dark bg-dark text-info float-right rounded mr-1" id="updateLista">
          <i class="material-icons align-middle">sync</i>
        </a>
      </div>
      <div class="card-body">
        <blockquote class="blockquote mb-0">
          <div class="table-responsive">
              <table class="table table-hover" id="paiTableLista">
                  <thead>
                    <th class="aloneName" colspan='2'>Item</th>
                    <th class="text-right pr-4 groupValue">Qtd</th>
                    <th class="text-right pr-4 groupValue">R$</th>
                    <th class="text-right pr-3 toggle-view"><span class="material-icons align-middle border rounded p-1" onclick="toggleView($(this))">compare_arrows</span></th>
                  </thead>
                  <tbody id="tableLista">
                  </tbody>
              </table>
          </div>
          <footer class="blockquote-footer">Desenvolvido por <cite title="Source Title">Mateus Brandão</cite> (2/5/20)</footer>
        </blockquote>
      </div>
    </div>
    <!-- Gerenciar Lista -->
    <div class="card mb-3" id="lGerencia" style="display: none;">
      <div class="card-header bg-dark text-center text-light font-weight-bold t-dark">
        <span class="material-icons align-middle">list_alt</span> Gerenciar Lista
      </div>
      <div class="card-body">
        <blockquote class="blockquote mb-0">
          <div class="form-group">
              <select class="custom-select"><option>Alterar um determinado item</option></select>
          </div>
          <div>
              <ul class="list-group small">
                  <li class="list-group-item t-dark bg-dark text-light text-center font-weight-bold">
                    <span class="material-icons align-middle">save</span> Listas Salvas
                    <span class="material-icons align-middle text-warning" onclick="window.onbeforeunload = true; window.location.href='back.php?update';">donut_large</span>
                  </li>
                  
                  <?php
                    $file = 'listas/'.$identify.'_detail.php';
                    if(file_exists($file)){
                      echo implode('',file($file));
                    }
                  ?>
              </ul>
          </div>
          <footer class="blockquote-footer mt-1">Desenvolvido por <cite title="Source Title">Mateus Brandão</cite> (2/5/20)</footer>
        </blockquote>
      </div>
    </div>
  </div>
  <!--Modal-->
  <!-- Modal Adicionar a Lista -->
  <div class="modal fade" tabindex="-1" role="dialog" id="modalCadastrar" aria-labelledby="">
      <div class="modal-dialog" role="document">
          <div class="modal-content modal-light">
              <div class="modal-header">
                  <h5 class="modal-title"><span class="material-icons align-middle">shopping_cart</span> Adicionar a Lista</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="back.php" id="formCadastrar">
                    <button type="button" class="btn btn-warning btn-block btn-sm text-light mb-3" onclick="confirmacao('zerar');">Zerar Lista</button>
                    <div class="form-group">
                        <input type="text" class="form-control" id="nomeItem" placeholder="Digite o Item...">
                    </div>
                    <div class="form-group">
                        <input type="number" class="form-control" id="qtdItem" placeholder="Digite a Quantidade..." min=1 value="1">
                    </div>
                    <button type="button" class="btn btn-info btn-block btn-sm" onclick="addList();">Adicionar</button>
                    <ul class="list-group my-3" id="ulLista" style="overflow: auto; max-height: 250px;">
                        <li class="list-group-item bg-dark text-light text-center py-1 font-weight-bold t-dark">Lista</li>
                    </ul>
                    <button type="button" class="btn btn-danger btn-block btn-sm" onclick="saveList(false);">Salvar</button>
                    <input type="hidden" id="bdjs" name="bdjs">
                </form>
              </div>
          </div>
      </div>
  </div>
  <!-- Modal Salvar Lista -->
  <div class="modal fade" tabindex="-1" role="dialog" id="modalSave" aria-labelledby="">
      <div class="modal-dialog" role="document">
          <div class="modal-content modal-light">
              <div class="modal-header">
                  <h5 class="modal-title"><span class="material-icons align-middle">save</span> Salvar Lista</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="back.php" id="formSave">
                    <!--Nome-->
                    <div class="form-group">
                        <input type="date" value="<?php echo date('Y-m-d'); ?>" class="form-control" id="dataLista">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="nomeLista" placeholder="Digite o nome da Lista...">
                    </div>
                    <button type="button" class="btn btn-danger btn-block btn-sm" onclick="saveList(true);">Salvar</button>
                    <input type="hidden" id="arqjs" name="arqjs">
                    <input type="hidden" id="arqname" name="arqname">
                </form>
              </div>
          </div>
      </div>
  </div>
  <!-- Modal Msg -->
  <div class="modal fade" tabindex="-1" role="dialog" id="modalMsg" aria-labelledby="">
      <div class="modal-dialog" role="document">
        <div class="modal-content modal-light">
            <div class="modal-header">
                <h5 class="modal-title"><span class="material-icons align-middle text-danger">shopping_cart</span> Matth Mart</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center font-custom"></p>
            </div>
        </div>
      </div>
  </div>
  <!-- Modal Dinamic -->
  <div class="modal fade" tabindex="-1" role="dialog" id="modalDinamic" aria-labelledby="">
      <div class="modal-dialog" role="document">
        <div class="modal-content modal-light">
            <div class="modal-header">
                <h5 class="modal-title"><span class="material-icons align-middle text-danger">shopping_cart</span> Matth Mart</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            </div>
        </div>
      </div>
  </div>
  <?php
      include('../function/global.php');
  ?>
  <!-- Bootstrap core JavaScript -->
  <script src="../jquery/jquery.min.js"></script>
  <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>