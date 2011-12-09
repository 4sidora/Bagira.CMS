<?

$gbl_config[ttffont] = "lib/grafics/tahoma.ttf";
$gbl_config[ttffont_bld] = "lib/grafics/tahoma_bold.ttf";


define ('pie_none',0);                // Нет легенды
define ('pie_legend_pcent',1);                // Процент легенды
define ('pie_legend_value',2);        // Значение легенды
define ('pie_chart_value',4);                // Значение диаграмы
define ('pie_chart_pcent',8);                // Процент диаграмы

define ('line_mark_none',0);                // Линия без всего
define ('line_mark_plus',1);                // Линия с плюсами
define ('line_mark_x',2);                // Линия с крестами
define ('line_mark_circle',3);                // Линия с кружками
define ('line_mark_square',4);        // Линия с квадратами
define ('line_mark_diamond',5);        // Линия с ромбами

define ('cols_no_stack',0);
define ('cols_stacked',1);

class Graph  {

        var $image;

        var $title,$subtitle;
        var $bgd,$cbgd,$cbgd2;
        var $txtcol,$black,$gridcol;
        var $width,$height;
        var $cwidth,$cheight,$scolors;
        var $lm,$prm,$rm,$tm,$bm;
        var $ytitle,$ymin=0,$ymax=0;
        var $ygrid=false,$ygridint,$ystr=0,$yline=1,$ydashed=0;
        var $xtitle,$xgrid=false,$xstr=0,$xgridint,$xline=1,$xdashed=0;
        var $xcount,$seriescount,$colwidth;
        var $ispiechart, $period, $stop;
        var $legends,$stackcount,$xlabels,$xstep,$xdelta;
        var $data,$xmaxima,$xminima,$stypes;
        var $stacked,$stackbase,$s_line,$font=false;

/*===============================================================================
 Конструктор
===============================================================================*/
function Graph($awidth, $aheight=0) {

        $this->width = $awidth;
        $this->height = $aheight==0 ? floor($awidth / 1.616) : $aheight;
        $this->image = imagecreate($this->width, $this->height);
        $this->bgd = imagecolorallocate($this->image,255,255,255);
        $this->cbgd = imagecolorallocate($this->image,255,255,255);
        $this->cbgd2 = imagecolorallocate($this->image,255,255,255);
        $this->txtcol = imagecolorallocate($this->image,92,79,36);
        $this->black = imagecolorallocate($this->image,0,0,0);
        $this->gridcol = imagecolorallocate($this->image,144,125,66);
        $this->scolors = array (
                0 => imagecolorallocate ($this->image,102,148,218), //6694da
                1 => imagecolorallocate ($this->image,113,208,113), //71d071
                2 => imagecolorallocate ($this->image,230,225,205), //e6e1cd
                3 => imagecolorallocate ($this->image,234,219,55),  //eadb37
                4 => imagecolorallocate ($this->image,94,196,193),  //5ec4c1
                5 => imagecolorallocate ($this->image,194,106,106), //c26a6a
                6 => imagecolorallocate ($this->image,129,116,66),  //817442
                7 => imagecolorallocate ($this->image,250,150,100), //817442
                8 => imagecolorallocate ($this->image,30,140,160),  //1e8ca0
                9 => imagecolorallocate ($this->image,150,120,100)  //967864
        );

        $this->tm = 25;
        $this->bm = 80;
        $this->lm = 60;
        $this->rm = 60;
        $this->cwidth = $this->width - ($this->lm + $this->rm);
        $this->cheight = $this->height - ($this->tm + $this->bm);
        $this->seriescount = 0;
        $this->xcount = 0;
}

/*===============================================================================
 Установка шрифтов
===============================================================================*/
function setFonf($font=false) {

        if ($font) $this->font = $font;
}

/*===============================================================================
 Установка заголовка
===============================================================================*/
function setTitle($aTitle,$aSub='') {
        //$aSub = date("d/m/Y H:i");
        if ($aTitle) $this->title = $aTitle;
        if ($aSub) $this->subtitle = $aSub;
}

/*===============================================================================
 Установка оси Y
===============================================================================*/
function setYAxis($title='',$min=0,$max=0,$gridint=0,$grid=0, $Ystr=0, $Yline=1, $Ydashed=0) {

        $this->ytitle = $title;
        $this->ymin = $min;
        $this->ymax = $max;
        $this->ygrid = $grid;
        $this->ygridint = $gridint;
        if ($Ystr) $this->ystr = $Ystr;
        if ($Yline) $this->yline = $Yline;
        if ($Ydashed) $this->ydashed = $Ydashed;

}

/*===============================================================================
 Установка оси X
===============================================================================*/
function setXAxis($title, $grid=0, $Xstr=0, $Xline=1, $Xdashed=0) {

        $this->xtitle = $title;
        $this->xgrid = $grid;
        if ($Xstr) $this->xstr = $Xstr;
        if ($Xline) $this->xline = $Xline;
        if ($Xdashed) $this->xdashed = $Xdashed;
}

/*===============================================================================
 Установка подписей X
===============================================================================*/
function setXLabels($labs, $step, $delta) {

        $this->xlabels = is_array($labs) ? $labs : explode(",",$labs);
        $this->xcount = count($this->xlabels);
        $this->xstep = $step;
        $this->xdelta = $delta;
}

/*===============================================================================
 Установка данных для графика
===============================================================================*/
function addDataSeries($type,$vals,$legend, $s_line=1) {

        // Вид графика
        switch ($type) {
                case 'L':
                        $stacked = 0;

                break;
                case 'P':

                        $this->bgd = imagecolorallocate($this->image,255,255,255);
                                $this->cbgd = imagecolorallocate($this->image,255,255,255);
                                $this->cbgd2 = imagecolorallocate($this->image,229,230,213);
                                $this->txtcol = imagecolorallocate($this->image,108,108,108);
                                $this->black = imagecolorallocate($this->image,0,0,0);
                                $this->gridcol = imagecolorallocate($this->image,128,128,128);
                        $stacked = 8;
                break;
                case 'C':
                        $stacked = 0;
                break;
        }

        $n = $this->seriescount++;
        $this->stypes[$n] = $type;
        $this->stacked[$n] = $stacked;
        $d = is_array($vals) ? $vals : explode(",",$vals);
        $this->xmaxima[$n] = max($d);
        $this->xminima[$n] = min($d);
        $dc = count($d);
        if ($this->xcount < $dc) {
                for ($i=$this->xcount,$L = 'A'; $i < $dc; $i++, $L++)
                $this->xlabels[$i] = $L;
                $this->xcount = $dc;
        }
        if ($dc < $this->xcount) array_pad($d,$this->xcount,0);
        $this->data[$n] = $d;
        $this->legends[$n] = $legend=='' ? '_' : $legend;
        $this->s_line[$n] = $s_line;
        //$fd = fopen ('d.txt', "a");  $contents = fwrite ($fd, $d[1]."\n"); fclose ($fd);
}

/*===============================================================================
 Прорисовка / сохранение графика
===============================================================================*/
function drawGraph($filename) {

        header("Content-Type: image/png");
        $this->_draw();

        if ($filename!='')        { imagepng( $this->image, $filename ); }
        else                { imagepng( $this->image); }
        imagedestroy( $this->image );

         header("Content-Type: text/html; charset=windows-1251");
}


/*===============================================================================
================================================================================
===============================================================================*/
function _draw() {

        $a = array_keys($this->stypes,'P');
        $this->ispiechart = count($a)>0;
        $this->_calcrmargin();
        imagefilledrectangle($this->image,0,0,$this->width-1,$this->height-1,$this->bgd);
        imagefilledrectangle($this->image, $this->lm, $this->tm, $this->width-$this->rm, $this->height-$this->bm, $this->cbgd);
        $this->stackcount=0;
        for ($s=0; $s<$this->seriescount; $s++) {
                if (($this->stypes[$s] == 'C') && ($this->stacked[$s]==1)) $this->stackcount++;
        }

        $this->_drawtitles();
        $this->_drawaxes();
        $this->_drawlegends();
        for ($i=0; $i<$this->seriescount; $i++) { $this->_plotSeries($i); }

        $maxc = max($maxc, strlen($this->subtitle)+5);
        if ($this->stypes[0] != 'P') {
                $this->_writestring ($this->image,2,$this->cwidth-$maxc+100, $this->height-$maxc-220, $this->subtitle, $this->gridcol,0,90);
        } else {
                $this->_writestring ($this->image,2,$this->cwidth-$maxc-10, $this->height-20, $this->subtitle, $this->gridcol,0,0);
        }
}

function _drawpieval($i,$alpha,$x,$y,$r) {

//global $tan;
        $pietot = 0;
        for ($s=0; $s<$this->seriescount; $s++) {
                if ($this->stypes[$s]=='P') $pietot += array_sum($this->data[$s]);
        }
        $val = array_sum($this->data[$i]);
        $pc = sprintf('%0.1f %%',$val * 100 / $pietot);
        $tx = $x + $r*1.15 * cos(deg2rad($alpha));
        $ty = $y + $r*1.15 * sin(deg2rad($alpha));
        $fw = imagefontwidth(2);

        switch (($this->stacked[$i]>>2) & 0x3) {
                case 1:
                        $tw = strlen($val)*$fw;
                        $tx -= $tw/2;
                        imagefilledrectangle($this->image,$tx-2,$ty-6,$tx + $tw + 2,$ty+6,$this->white);
                        $this->_writestring($this->image,2,$tx,$ty-5,$val,$this->txtcol,0,0);
                break;
                case 2:
                case 3:

                        $tw = strlen($pc)*$fw;
                        $tx -= $tw/2;
                        imagefilledrectangle($this->image,$tx-2,$ty-6,$tx + $tw + 2,$ty+6,$this->white);
                        $this->_writestring($this->image,2,$tx,$ty-5,$pc,$this->txtcol,0,0);
                break;
        }
}

function pielegends() {

        $pietot = 0;
        for ($s=0; $s<$this->seriescount; $s++) {
                if ($this->stypes[$s]=='P') $pietot += array_sum($this->data[$s]);
        }
        $a = array_keys($this->stypes,'P');
        $maxt = $maxv = 0;
        foreach ($a as $pie) {
                $val = array_sum($this->data[$pie]);
                $maxv = max($maxv, strlen($val));
                $txt = $this->legends[$pie];
                $maxt = max($maxt, strlen($txt));
        }
        foreach ($a as $pie) {
                $val = array_sum($this->data[$pie]);
                $pc = $val * 100 / $pietot;
                switch ($this->stacked[$pie] & 0x03) {
                        case 1 : $this->legends[$pie] = sprintf("%-{$maxt}s %4.1f%%",$this->legends[$pie],$pc); break;
                        case 2 : $this->legends[$pie] = sprintf("%-{$maxt}s %{$maxv}s",$this->legends[$pie],$val); break;
                        case 3 : $this->legends[$pie] = sprintf("%-{$maxt}s %{$maxv}s %4.1f%%",$this->legends[$pie],$val,$pc); break;
                }
        }
}

function _calcrmargin() {

        if ($this->ispiechart) {
                $this->pielegends();
        }

        $leglen = array();
        foreach($this->legends as $leg) {
                $leglen[] = strlen($leg);
        }
        $maxleglen = max($leglen);
        if ($maxleglen==0) return;
        $legwid = $maxleglen * imagefontwidth(2);
        $this->prm = max($this->prm, $legwid);


        $this->cwidth = $this->width - $this->lm - $this->rm;
        $this->xgridint = ($this->cwidth / $this->xcount);
        $maxlabwid = $this->_maxlab();
        if ($maxlabwid > $this->xgridint) {
                $this->bm = max($this->bm,$maxlabwid+40);
                $this->cheight = $this->height - $this->tm - $this->bm;
        }
}

function _drawlegends() {

//global $tan;

        $legx = $this->lm;
        $legy = $this->tm + $this->cheight+35;

        $legx_p = $this->lm + $this->cwidth - $this->prm;
        $legy_p = $this->tm+50;

        if ($this->stackcount>0) {
                $this->legends = array_reverse($this->legends,true);
        }

        foreach ($this->legends as $k=>$leg)
          $rto += $this->data[$k][0];

        foreach ($this->legends as $k=>$leg) {
                if ($leg != '_') {

                        $leglen_t = array();
                        foreach($this->legends as $leg_t) {
                                $leglen_t[] = strlen($leg_t);
                        }
                        $maxleglen = max($leglen_t);
                        $legwid_t = $maxleglen * imagefontwidth(2) + 50;

                        if (!($k%3) && $k!=0) {
                                $legx = $legx + $legwid_t;
                                $legy = $this->tm + $this->cheight+35;
                        }

                        switch ($this->stypes[$k]) {
                                case 'C':
                                        if ($this->ispiechart) break;
                                        imagefilledrectangle($this->image,$legx,$legy,$legx+10,$legy+10,$this->scolors[$k]);
                                        imagerectangle($this->image,$legx,$legy,$legx+10,$legy+10,$this->black);
                                        $this->_writestring($this->image,2,$legx+20,$legy-1,$leg,$this->txtcol,0,0);
                                break;
                                case 'P':
                                        imagefilledrectangle($this->image,$legx_p,$legy_p,$legx_p+12,$legy_p+12,$this->scolors[$k]);
                                        imagerectangle($this->image,$legx_p,$legy_p,$legx_p+12,$legy_p+12,$this->black);

                                        $this->_writestring($this->image,2,$legx_p+20,$legy_p,$leg.' ('.
                                        sprintf('%0.1f %%',$this->data[$k][0] * 100 / $rto)
                                        .')',$this->txtcol,0,0);
                                break;
                                case 'L':
                                        if ($this->ispiechart) break;
                                        if ($this->stacked[$k]>=0) {
                                                imageline($this->image,$legx-5,$legy+7,$legx+20,$legy+7,$this->scolors[$k]);
                                                imageline($this->image,$legx-5,$legy+8,$legx+20,$legy+8,$this->scolors[$k]);
                                                imageline($this->image,$legx-5,$legy+9,$legx+20,$legy+9,$this->scolors[$k]);
                                        }
                                        $this->_drawmarker($legx+8,$legy+8,$this->stacked[$k], $this->scolors[$k]);
                                        $this->_writestring($this->image,2,$legx+25,$legy+1,$leg,$this->txtcol,0,0);
                        }
                        $legy += 15;
                        $legy_p += 20;
                }
        }
}

function _val2y($v, $base=0) {

        $rv = $v + $base - $this->ymin;
        $ppu = $this->cheight/($this->ymax - $this->ymin);

return $this->tm + $this->cheight - ($rv * $ppu) ;
}

function _drawmarker($x,$y,$m, $c) {

        $fill = $m < 0 ? $c : $this->white;
        $line = $m < 0 ? $c : $this->black;
        switch (abs($m)) {
                case 1:
                        $x0 = $x-3; $x1 = $x+3; imageline($this->image,$x0,$y,$x1,$y,$line);
                        $y0 = $y-3; $y1 = $y+3; imageline($this->image,$x,$y0,$x,$y1,$line);
                break;
                case 2:
                        $x0 = $x-3; $x1 = $x+3;
                        $y0 = $y-3; $y1 = $y+3;
                        imageline($this->image,$x0,$y0,$x1,$y1,$line);
                        imageline($this->image,$x1,$y0,$x0,$y1,$line);
                break;
                case 3:
                        $w = $h = 8;
                        imagearc($this->image,$x,$y,$w,$h,0,361,$this->black);
                        imagefill($this->image,$x,$y,$fill);
                break;
                case 4:
                        $x0 = $x-3; $x1 = $x+3;
                        $y0 = $y-3; $y1 = $y+3;
                        imagefilledrectangle($this->image,$x0,$y0,$x1,$y1,$fill);
                        imagerectangle($this->image,$x0,$y0,$x1,$y1,$this->black);
                break;
                case 5:
                        $p[] = $x; $p[] = $y-4; $p[] = $x+4; $p[] = $y;
                        $p[] = $x; $p[] = $y+4; $p[] = $x-4; $p[] = $y;
                        imagefilledpolygon($this->image,$p,4,$fill);
                        imagepolygon($this->image,$p,4,$this->black);
        }
}

function _plotseries($i) {

        switch ($this->stypes[$i]) {
                case 'L':
                        if ($this->ispiechart) break;
                        for ($p=0; $p<$this->xcount; $p++) {
                                $pts[$p][0] = $this->lm + $p * $this->xgridint + $this->xgridint/2;
                                $pts[$p][1] = $this->_val2y($this->data[$i][$p]);
                        }
                        for ($p=1; $p<$this->xcount; $p++) {
                                if (!isset($this->data[$i][$p])) continue;
                                if ($this->stacked[$i]<0) continue;

                                ImageSetThickness($this->image, $this->s_line[$i]);


                                if ($this->period == 'last') {
                                        imageline($this->image,$pts[$p-1][0],$pts[$p-1][1],$pts[$p][0],$pts[$p][1],$this->scolors[$i]);
                                }
                                elseif ($this->period == 'now' && $p<=$this->stop) {
                                        imageline($this->image,$pts[$p-1][0],$pts[$p-1][1],$pts[$p][0],$pts[$p][1],$this->scolors[$i]);
                                }

                                ImageSetThickness($this->image, 1);
                        }
                        if ($this->stacked[$i]!=0) {
                                for ($p=0; $p<$this->xcount; $p++) {
                                        if (!isset($this->data[$i][$p])) continue;
                                        $this->_drawmarker($pts[$p][0],$pts[$p][1],$this->stacked[$i], $this->scolors[$i]);
                                }
                        }
                break;
                case 'C':
                        if ($this->ispiechart) break;
                        $stacked = $this->stackcount>0;
                        $b = array_keys($this->stypes,'C');
                        $colpos = 0;
                        if (!$stacked){
                                while (list($k,$v)=each($b)) {
                                        if ($v==$i) break;
                                        $colpos++;
                                }
                        }
                        for ($p=0; $p<$this->xcount; $p++) {
                                $x0 = $this->lm + 5 + $p * $this->xgridint + $colpos * $this->colwidth;
                                $x1 = $x0 + $this->colwidth;
                                $y0 = $this->_val2y($this->data[$i][$p],$this->stackbase[$p]);
                                $y1 = $this->_val2y($this->ymin,$this->stackbase[$p]);
                                if ($stacked) $this->stackbase[$p] += $this->data[$i][$p];
                                imagefilledrectangle($this->image,$x0,$y0,$x1,$y1,$this->scolors[$i]);
                                //imagerectangle($this->image,$x0,$y0,$x1,$y1,$this->black);
                        }
                break;
                case 'P':
                        $x = ($this->lm + $this->cwidth + $this->lm)/2-70;
                        $y = ($this->tm + $this->cheight + $this->tm)/2+20;
                        $w = $h = min($this->cwidth, $this->cheight) +40;
                        $pietot = 0;
                        for ($s=0; $s<$this->seriescount; $s++) {
                                if ($this->stypes[$s]=='P') $pietot += array_sum($this->data[$s]);
                        }
                        $alpha = $this->stackbase[0];
                        $theta = array_sum($this->data[$i])*360 / $pietot;
                        imagefilledarc($this->image,$x,$y,$w,$h,$alpha,$alpha+$theta,$this->scolors[$i],IMG_ARC_PIE);
                        $this->_drawpieval($i,$alpha+$theta/2,$x,$y,$w/2);

                       // $tan[] = _drawpieval($i,$alpha+$theta/2,$x,$y,$w/2);
                      //  global $tan;
                        $this->stackbase[0] += $theta;
                break;
        }
}

function _drawtitles() {

        $cw = imagefontwidth(5);
        $l = strlen($this->title);
        $tw = $cw*$l;
        $x = $this->lm + ($this->cwidth - $tw)/2;
        $this->_writestring ($this->image,5,$x, 5, $this->title, $this->txtcol, 1,0);
        $cw = imagefontwidth(4);
        $l = strlen($this->subtitle);
        $tw = $cw*$l;
        $x = $this->lm + ($this->cwidth - $tw)/2;
}

function _drawaxes() {

        if ($this->ispiechart) return;
        $changed = 0;
        $ym = $this->_calcymax();
        if ($this->ymax < $ym ) {
                $this->ymax = $ym;
                $changed = 1;
        }
        $yn = min($this->xminima);
        if ($this->ymin > $yn) {
                $this->ymin = $yn;
                $changed = 1;
        }
        if ($changed) $this->ygridint = ($this->ymax - $this->ymin)/4;
        $this->_drawyaxis();
        $this->_drawxaxis();
}

function _drawyaxis() {

        $x0 = $this->lm;
        $y0 = $this->tm + $this->cheight;
        $x1 = $x0;
        $y1 = $this->tm;
        $div = $this->ygridint * $this->cheight / ($this->ymax - $this->ymin);
        $grx = $this->ygrid ? $this->lm + $this->cwidth : $this->lm-3;
        for ($y = $y1,$v=$this->ymax,$i=0; $y < $y0-2; $y += $div, $v -= $this->ygridint, $i++) {
                if ($this->cbgd != $this->cbgd2) {
                        $col = ($i%2) ? $this->cbgd : $this->cbgd2;
                        imagefilledrectangle($this->image,$x0,$y,$this->lm + $this->cwidth,$y+$div,$col);
                }
                if ($this->ydashed) {
                        for ($yd=$x0;$yd<=$grx; $yd=$yd+4) {
                                imagesetpixel($this->image,$yd,$y,$this->gridcol);
                        }
                } else {
                        imageline($this->image,$x0,$y,$grx,$y,$this->gridcol);
                }
                imageline($this->image,$x0-4,$y,$x0+3,$y,$this->txtcol);
                $tw = strlen("$v")*imagefontwidth(2);
                $this->_writestring($this->image,2,$x0 - $tw  - 10, $y - 5, $v, $this->txtcol,0,0);
        }
        $tw = strlen("$this->ymin")*imagefontwidth(2);
        $y = $this->tm + $this->cheight;
        $this->_writestring($this->image,2,$x0 - $tw  - 10, $y - 6, $this->ymin, $this->txtcol,0,0);
        imageline($this->image,$x0,$y0,$x1,$y1,$this->gridcol);
        $tw = strlen($this->ytitle) * imagefontwidth(3);
        $y = ($this->tm + $this->height - $this->bm + $tw)/2;
        $x = 10;
        $this->_writestring($this->image,3,$x0-$tw-8,$this->tm-25,$this->ytitle,$this->txtcol,1,0);

        if ($this->ystr) {
                $points[0] = $this->lm;
                $points[1] = $this->tm-20;
                $points[2] = $points[0]+2;
                $points[3] = $points[1]+15;
                $points[4] = $points[2]-2;
                $points[5] = $points[3]-2;
                $points[6] = $points[2]-4;
                $points[7] = $points[3];
                ImageSetThickness($this->image, $this->yline);
                imageline($this->image,$x1,$y0,$x1,$y1-10,$this->txtcol);
                ImageSetThickness($this->image, 1);
                ImageFilledPolygon($this->image,$points,4,$this->txtcol);
        }
}

function _maxlab() {

        $max = 0;
        for ($i=0; $i < $this->xcount; $i++) {
                $v = $this->xlabels[$i];
                $tw = strlen("$v")*imagefontwidth(2);
                $max = max ($max, $tw);
        }

return $max;
}

function _drawxaxis() {

        $maxlabwid = $this->_maxlab();
        $x0 = $this->lm;
        $y0 = $this->tm + $this->cheight;
        $x1 = $x0 + $this->cwidth;
        $y1 = $y0;
        $div = $this->xgridint;
        imageline($this->image,$x0,$y0,$x1,$y1,$this->gridcol);
        $gry = $this->xgrid ? $this->tm : $this->tm + $this->cheight + 3 ;
        $ii=0;
        for ($x=$x1,$i=$this->xcount-1; $x>$x0+3; $x -= $div, $i--, $ii++) {
                if ($this->xdashed) {
                        for ($xd=$gry;$xd<=$y0; $xd=$xd+4) {
                                imagesetpixel($this->image,$x,$xd,$this->gridcol);
                        }
                } else {
                        imageline($this->image,$x,$gry,$x,$y0,$this->gridcol);
                }
                imageline($this->image,$x,$y0-4,$x,$y0+3,$this->txtcol);
                $v = $this->xlabels[$i];
                $tw = strlen("$v")*imagefontwidth(2);
                $th = imagefontheight(2);

                if (!($ii%$this->xstep)) {
                        if ($this->xdelta) { if (!$delta) { $delta=12; } else { $delta=0; } } else { $delta=0; }

                        $this->_writestring($this->image,2,$x - ($div+$tw)/2, $y0 + 5 + $delta, $v, $this->txtcol,0,0);
                }
        }
        $y = $this->height - 30;
        $tw = strlen($this->xtitle)*imagefontwidth(3);
        $x = ($this->lm + $this->cwidth + $this->lm - $tw)/2;
        $this->_writestring($this->image,3,$this->lm + $this->cwidth+2,$y0-20,$this->xtitle,$this->txtcol,1,0);

        if ($this->xstr) {
                $points[0] = $this->lm + $this->cwidth+10;
                $points[1] = $y0-3;
                $points[2] = $points[0]+15;
                $points[3] = $points[1]+3;
                $points[4] = $points[2]-15;
                $points[5] = $points[3]+3;
                $points[6] = $points[4]+3;
                $points[7] = $points[5]-3;
                ImageSetThickness($this->image, $this->xline);
                imageline($this->image,$x0,$y0,$x1+15,$y1,$this->txtcol);
                ImageSetThickness($this->image, 1);
                ImageFilledPolygon($this->image,$points,4,$this->txtcol);
        }
}

function _calcymax() {

        $b = array_keys($this->stypes,'C');
        if ($this->stackcount == 0) {
                $m = max($this->xmaxima);
                if (count($b) > 0)
                        $this->colwidth = ($this->xgridint - 10)/count($b);
                else
                        $this->colwidth = 0;
        } else {
                $m=0;
                foreach ($b as $v) {
                        $m += $this->xmaxima[$v];
                        $this->stacked[$v] = 1;
                }
                $this->colwidth = ($this->xgridint - 10);
        }
return $m;
}

function _writestring($img,$size,$fx,$fy,$string,$color,$bold,$rotate) {

        if ($this->font) {
                if ($bold)
                {
                        $this->font = str_replace(".ttf", "_bold.ttf", $this->font);
                        $delta_sixe = 5;
                } else {
                        $this->font = str_replace("_bold.ttf", ".ttf", $this->font);
                        $delta_sixe = 6;
                }

                ImageTTFText($img,$size+$delta_sixe,$rotate,$fx,$fy+12,$color,$this->font, iconv("Windows-1251","UTF-8",$string) );

                //iconv("Windows-1251","UTF-8",$string)
        } else {
                if (!$rotate) {
                        imagestring($img,$size,$fx,$fy,$string,$color);
                } else {
                        imagestringup($img,$size,$fx,$fy,$string,$color);
                }
        }
}

}
?>