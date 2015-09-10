<?php

class PdfHtml extends FPDF
{
    var $B=0;
    var $I=0;
    var $U=0;
    var $HREF='';
    var $ALIGN='';

    var $lineHeight = 0;

    function footer()
    {
      // FIRMAS
      $texto = 'Firma del Director del Prestador de Servicios';
      $this->Cell(100, 5, utf8_decode($texto), 0, 0, 'L');
      $this->Cell(50, 5, '', 0, 0, 'L');
      $texto = 'Firma del Auditor de la Comisión Nacional';
      $this->Cell(100, 5, utf8_decode($texto), 0, 0, 'L');

      $this->Ln(5);

      $texto = 'de Formacion Profesional.';
      $this->Cell(15, 5, '', 0, 0, 'L');
      $this->Cell(50, 5, utf8_decode($texto), 0, 0, 'L');
      $texto = 'del Tránsito y la Seguridad Vial.';
      $this->Cell(95, 5, '', 0, 0, 'L');
      $this->Cell(90, 5, utf8_decode($texto), 0, 0, 'L');
    }

    function __construct ($orientation = 'P', $unit = 'pt', $format = 'Letter', $margin = 40, $lineHeight = 5) {
        $this->FPDF($orientation, $unit, $format);
        $this->SetTopMargin($margin);
        $this->SetLeftMargin($margin);
        $this->SetRightMargin($margin);
        $this->SetAutoPageBreak(true, $margin);

        $this->lineHeight = $lineHeight;
    }

    function WriteHTML($html)
    {
        //HTML parser
        $html=str_replace("\n",' ',$html);
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);

        //print_r($a);exit();

        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                //Text
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                elseif($this->ALIGN=='center')
                    $this->Cell(0,5,$e,0,1,'C');
                else
                    $this->Write($this->lineHeight,$e);
            }
            else
            {
                //Tag
                if($e[0]=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    //Extract properties
                    $a2=explode(' ',$e);
                    $tag=strtoupper(array_shift($a2));
                    $prop=array();
                    foreach($a2 as $v)
                    {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                            $prop[strtoupper($a3[1])]=$a3[2];
                    }
                    $this->OpenTag($tag,$prop);
                }
            }
        }
    }

    function OpenTag($tag,$prop)
    {
        //Opening tag
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,true);
        if($tag=='A')
            $this->HREF=$prop['HREF'];
        if($tag=='BR')
            $this->Ln(5);
        if($tag=='P')
            $this->ALIGN=$prop['ALIGN'];
        if($tag=='HR')
        {
            if( !empty($prop['WIDTH']) )
                $Width = $prop['WIDTH'];
            else
                $Width = $this->w - $this->lMargin-$this->rMargin;
            $this->Ln(2);
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetLineWidth(0.4);
            $this->Line($x,$y,$x+$Width,$y);
            $this->SetLineWidth(0.2);
            $this->Ln(2);
        }
    }

    function CloseTag($tag)
    {
        //Closing tag
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF='';
        if($tag=='P')
            $this->ALIGN='';
    }

    function SetStyle($tag,$enable)
    {
        //Modify style and select corresponding font
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s)
            if($this->$s>0)
                $style.=$s;
        $this->SetFont('',$style);
    }

    function PutLink($URL,$txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write($this->lineHeight,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }

    function crear_certificado($parametros) {
        $pdf = new PdfHtml('L', 'mm', 'Letter', 20, 7);
        $pdf->AddPage();

        $pdf->SetlineWidth(0.264583333);
        $pdf->setDrawColor(74, 112, 139);
        $pdf->Rect(17.01800, 4.318000, $pdf->w - 17.01800 - 17.01800, $pdf->h - 4.318000 - 4.318000);

        $pdf->SetFont('Arial', '', 12);

        // IMAGENES

        // QR
        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . "status_chofer.php";
        $qrFile = QR::generar($url);
        // $qrFile = generarQR($url);
        $pdf->Image($qrFile, 18, 8, 0, 30, 'PNG');
        unlink($qrFile);

        $pdf->Image(__DIR__ . '/escudo_nacion.png', 235, 8, 0, 30);
        $pdf->Image(__DIR__ . '/logo_cntsv.png', 105, 8, 0, 30);

        $pdf->Ln(15);

        // TITULO
        $title = '<p align="center"><b><u>CERTIFICADO DE CAPACITACIÓN PARA EL TRANSPORTE DE MERCANCÍAS PELIGROSAS</u></b></p>';

        $pdf->WriteHTML(utf8_decode($title));

        // BLOQUE 1
        $htmlprev = <<<'EOD'
El Prestador de Servicios de Formación Profesional para la Capacitación Básica y Complementaria Obligatoria de los Conductores de Vehículos Empleados
en el Transporte de Mercancías Peligrosas por Carretera, <b>#PRESTADOR</b>, certifica que el/la <b>#CHOFER</b>, Matricula <b>#MATRICULA</b>, DNI <b>#DNI</b>,
ha participado y completado el curso de Capacitación <b>#CURSO</b> según Resolución S.T N° 110/1997 modificada por Resolución S.T. N° 65/2000 para
los Conductores de los Vehículos antes mencionados.
EOD;

        $holders = ["#PRESTADOR", "#CHOFER", "#MATRICULA", "#DNI", "#CURSO"];
        $variables = [utf8_decode($parametros['prestador']), utf8_decode($parametros['chofer']), utf8_decode($parametros['matricula']), utf8_decode($parametros['dni']), utf8_decode($parametros['curso'])];
        $html = str_replace($holders, $variables, utf8_decode($htmlprev));

        $pdf->WriteHTML($html);

        $pdf->Ln(20);

        // BLOQUE 2
        $html = <<<'EOD'
Se expide el presente Certificado, a los efectos de la obtención de la Licencia Nacional habilitante.
La vigencia del mismo es de UN (1) año a partir de la fecha de realización del Curso de Capacitación.
EOD;

        $pdf->WriteHTML(utf8_decode($html));

        $pdf->Ln(20);

        // FECHAS
        $texto = 'Sede del Curso: ';
        $pdf->Cell(50, 5, utf8_decode($texto), 0, 0, 'L');
        $pdf->setFont('Arial', 'B', 12);
        $pdf->Cell(100, 5, utf8_decode($parametros['sede']), 0, 0, 'L');
        $pdf->setFont('Arial', '', 12);

        $texto = 'Fecha del Curso: ';
        $pdf->Cell(10, 5, '', 0, 0, 'L');
        $pdf->Cell(0, 5, $texto, 0, 0, 'L');
        $pdf->setFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, utf8_decode($parametros['fecha_curso']), 0, 1, 'R');
        $pdf->setFont('Arial', '', 12);

        $pdf->Ln(5);

        $texto = 'Numero de Transacción: ';
        $pdf->Cell(50, 5, utf8_decode($texto), 0, 0, 'L');
        $pdf->setFont('Arial', 'B', 12);
        $pdf->Cell(100, 5, utf8_decode($parametros['transaccion']), 0, 0, 'L');
        $pdf->setFont('Arial', '', 12);

        $texto = 'Fecha de Transacción: ';
        $pdf->Cell(10, 5, '', 0, 0, 'L');
        $pdf->Cell(0, 5, utf8_decode($texto), 0, 0, 'L');
        $pdf->setFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, utf8_decode($parametros['fecha_transaccion']), 0, 1, 'R');
        $pdf->setFont('Arial', '', 12);

        $pdf->Ln(45);

        return $pdf;
    }
}
