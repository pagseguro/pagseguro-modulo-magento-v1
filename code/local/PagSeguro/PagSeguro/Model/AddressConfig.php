<?php

class AddressConfig
{

  static function dados($v){
    $dados=array();
    $dados['complementos']=array("casa","ap","apto","apart","frente","fundos","sala","cj");
    $dados['brasilias']=array("bloco","setor","quadra","lote");
    $dados['naobrasilias']=array("av","avenida","rua","alameda","al.","travessa","trv","praça","praca");
    $dados['sems']=array("sem ","s.","s/","s. ","s/ ");
    $dados['numeros']=array('n.º','nº',"numero","num","número","núm","n");
    $dados['semnumeros']=array();
    foreach($dados['numeros'] as $n)
      foreach($dados['sems'] as $s)
      $dados['semnumeros'][]="$s$n";
    return $dados[$v];
  }

  static function endtrim($e){
    return preg_replace('/^\W+|\W+$/','',$e);
  }

  static function ehBrasilia($end){
    $brasilias=self::dados('brasilias');
    $naobrasilias=self::dados('naobrasilias');
    $brasilia=false;
    foreach($brasilias as $b)
      if(strpos(strtolower($end),$b)!=false)
        $brasilia=true;
    if($brasilia)
      foreach($naobrasilias as $b)
        if(strpos(strtolower($end),$b)!=false)
          $brasilia=false;
    return $brasilia;
  }

  static function ordenaDados($texto){
    $quebrado=preg_split('/[-,\\n]/',$texto);
    
    for($i=0;$i<strlen($quebrado[0]);$i++){
        if(is_numeric(substr($quebrado[0],$i,1))){
            return array(
                substr($quebrado[0],0,$i),
                substr($quebrado[0],$i),
                $quebrado[1]
                );
        }
    }
          
    $texto = preg_replace('/\s/',' ',$texto);
    $encontrar=substr($texto,-strlen($texto));
    for($i=0;$i<strlen($texto);$i++){
      if(is_numeric(substr($encontrar,$i,1))){
        return array(
            substr($texto,0,-strlen($texto)+$i),
            substr($texto,-strlen($texto)+$i),
            ''
            );
      }
    }
    
    return array(
        $texto,
        '',
        ''
    );
  }

  static function tiraNumeroFinal($endereco){
    $numeros=self::dados('numeros');
    foreach($numeros as $n)
      foreach(array(" $n"," $n ") as $N)
      if(substr($endereco,-strlen($N))==$N)
        return substr($endereco,0,-strlen($N));
    return $endereco;
  }

  static function separaNumeroComplemento($n){
    $semnumeros=self::dados('semnumeros');
    $n=self::endtrim($n);
    foreach($semnumeros as $sn){
      if($n==$sn)return array($n,'');
      if(substr($n,0,strlen($sn))==$sn)return array(substr($n,0,strlen($sn)),substr($n,strlen($sn)));
    }
    $q=preg_split('/\D/',$n);
    $pos=strlen($q[0]);
    return array(substr($n,0,$pos),substr($n,$pos));
  }

  static function brasiliaSeparaComplemento($end){
    $complementos=self::dados('complementos');
    foreach($complementos as $c)
      if($pos=strpos(strtolower($end),$c))
        return array(substr($end,0,$pos),substr($end,$pos));
    return array($end,'');
  }

  static function trataEndereco($end){
    $numeros=self::dados('numeros');
    $complementos=self::dados('complementos');
    
    if(self::ehBrasilia($end)){
      $numero='s/nº';
      list($endereco,$complemento)=self::brasiliaSeparaComplemento($end);
    }else{
      $endereco=$end;
      $numero='s/nº';
      $complemento='';
      $bairro='';
      
      $quebrado=preg_split('/[-,\\n]/',$end);
      
      if(sizeof($quebrado)==4){ list($endereco,$numero,$complemento,$bairro)=$quebrado;
      } elseif(sizeof($quebrado)==3){ list($endereco,$numero,$complemento)=$quebrado;
      } elseif(sizeof($quebrado)== 2 || sizeof($quebrado)== 1) {
        list($endereco,$numero,$complemento)=self::ordenaDados($end);
        } else {
          $endereco = $end;
      }
      
      $endereco=self::tiraNumeroFinal($endereco);
      
      //if($complemento=='')list($numero,$complemento)=self::separaNumeroComplemento($numero);
    }
      
    return array(self::endtrim($endereco),self::endtrim($numero),self::endtrim($complemento),self::endtrim($bairro));
  }
  
  static function trataEstado($estadoOriginal){
      
    $siglas = array('acre' => 'AC',
            'alagoas' => 'AL',
            'amapa' => 'AP', 
            'amazonas' => 'AM', 
            'bahia' => 'BA', 
            'ceara' => 'CE', 
            'espiritosanto' => 'ES', 
            'goias' => 'GO', 
            'maranhao' => 'MA', 
            'matogrosso' => 'MT', 
            'matogrossodosul' => 'MS',
            'matogrossosul' => 'MS', 
            'minasgerais' => 'MG', 
            'para' => 'PA', 
            'paraiba' => 'PB', 
            'parana' => 'PR' ,
            'pernambuco' => 'PE', 
            'piaui' => 'PI', 
            'riodejaneiro' => 'RJ', 
            'riojaneiro' => 'RJ', 
            'riograndedonorte' => 'RN', 
            'riograndenorte' => 'RN' ,
            'riograndedosul' => 'RS', 
            'riograndesul' => 'RS', 
            'rondonia' => 'RO', 
            'roraima' => 'RR', 
            'santacatarina' => 'SC', 
            'saopaulo' => 'SP', 
            'sergipe' => 'SE', 
            'tocantins' => 'TO', 
            'distritofederal' => 'DF');
      
    if(strlen($estadoOriginal) == 2) {
        foreach($siglas as $key=>$val){
            if($val == strtoupper($estadoOriginal)) {
                return strtoupper($estadoOriginal);            
            }
        }
        return '';
    }
      
    $estado=utf8_decode($estadoOriginal);
    $estado = strtolower($estado);
    // Código ASCII das vogais
    $ascii['a'] = range(224, 230);
    $ascii['e'] = range(232, 235);
    $ascii['i'] = range(236, 239);
    $ascii['o'] = array_merge(range(242, 246), array(240, 248));
    $ascii['u'] = range(249, 252);
 
    // Código ASCII dos outros caracteres
    $ascii['b'] = array(223);
    $ascii['c'] = array(231);
    $ascii['d'] = array(208);
    $ascii['n'] = array(241);
    $ascii['y'] = array(253, 255);
    
    $acentuacoes = array();
 
    foreach ($ascii as $key=>$item) {
        $acentos = '';
        foreach ($item AS $codigo) $acentos .= chr($codigo);
        $troca[$key] = '/['.$acentos.']/i';
    }
 
    $estado = preg_replace(array_values($troca), array_keys($troca), $estado);
 
    // Slug?
    if ($slug) {
        // Troca tudo que não for letra ou número por um caractere ($slug)
        $estado = preg_replace('/[^a-z0-9]/i', $slug, $estado);
        // Tira os caracteres ($slug) repetidos
        $estado = preg_replace('/' . $slug . '{2,}/i', $slug, $estado);
        $estado = trim($estado, $slug);
    }
      
    $estado = preg_replace("/\s/", "", $estado);
    
    foreach($siglas as $key=>$val){
        if($key == $estado) {
            $sigla = $val;
            return $sigla;
        }
    }
    
    return '';
  }
}