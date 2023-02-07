<?PHP

if (count($argv)!=2) { echo("Syntax: php ./streetview-dl.php <url>\nremember put url between single quotes\n"); exit(0); }

$id = $argv[1];

if (strpos($id, "/")!==false) $id = getIdFromUrl($id);

if (strlen($id)<10 || strlen($id)>30) { echo("wrong id\n"); exit(0); }

echo("pano id: ".$id."\n");

$op_st = new OpSt($id);

//$op_st->printDebug();

$op_st->download();

$pathL = "stl-".$id.".jpg";

echo($pathL." created\n");

echo("resize...\n");

$pathM = "stm-".$id.".jpg";
resizeEqui($pathL, $pathM, 8192);

echo($pathM." created\n");

$pathS = "sts-".$id.".jpg";
resizeEqui($pathM, $pathS, 1300);

echo($pathS." created\n");

echo("adding equirectangular pano exif tags\n");
addExifEqui($pathL, 13000);
addExifEqui($pathM, 8192);
addExifEqui($pathS, 1300);

echo("done\n");
echo("Support future improvements of this software https://www.buymeacoffee.com/fdd4s\n");

function resizeEqui($pathSrc, $pathDst, $width) {
	$height = $width / 2;
	exec("convert \"".$pathSrc."\" -resize ".$width."x".$height." -quality 100 \"".$pathDst."\"");
}

function getIdFromUrl($url) {
	$fields = explode("!1s", $url);
	$fields2 = explode("!", $fields[1]);
	return $fields2[0];
}

function addExifEqui($path, $width) {
	$height = $width / 2;
	exec("exiftool -overwrite_original -UsePanoramaViewer=True -ProjectionType=equirectangular -PoseHeadingDegrees=180.0 -CroppedAreaLeftPixels=0 -FullPanoWidthPixels=".$width." -CroppedAreaImageHeightPixels=".$height." -FullPanoHeightPixels=".$height." -CroppedAreaImageWidthPixels=".$width." -CroppedAreaTopPixels=0 -LargestValidInteriorRectLeft=0 -LargestValidInteriorRectTop=0 -LargestValidInteriorRectWidth=".$width." -LargestValidInteriorRectHeight=".$height." -Model=\"github fdd4s streetview-dl\" \"".$path."\"");
}

class OpSt {
	var $op_id;
	var $op_url_list;

	public function OpSt($id) {
		$this->op_id = new OpId($id);
		$this->op_url_list = new OpUrlList();
	}

	public function download() {
		echo("Downloading...\n");
		$this->makeImgList();
		$this->op_url_list->downloadAria2c();
		echo("Montage...\n");
		$this->makeMontage();
		echo("Cleaning temp files...\n");
		$this->op_url_list->removeFiles();
	}

	private function makeImgList() {
		$this->op_url_list->clear();

		$x_ini = 0;
		$x_fin = 25;
		$y_ini = 0;
		$y_fin = 12;

		$num_file = 1;

		$codigo = $this->op_id->getIdSrc();
		$id = $this->op_id->getIdOp();

		for ($y_act = $y_ini; $y_act <= $y_fin; $y_act++) {
			for ($x_act = $x_ini; $x_act <= $x_fin; $x_act++) {
				$url = "https://streetviewpixels-pa.googleapis.com/v1/tile?cb_client=maps_sv.tactile&panoid=".$codigo."&x=".$x_act."&y=".$y_act."&zoom=5&nbt=1&fover=2";

				$file = "tmp-f".$id."_".$num_file.".jpg";
				$num_file++;

				$this->op_url_list->addUrl($url, $file);
			}
		}
	}

	public function makeMontage() {
		$file_list_data = "";
		$id = $this->op_id->getIdOp();
		$idSrc = $this->op_id->getIdSrc();
		for ($i=1; $i<339; $i++) {
			$file_list_data .= "tmp-f".$id."_".$i.".jpg\n";
		}
		$file_list_path = dirname(__FILE__)."/tmp-fl".$id.".txt";
		file_put_contents($file_list_path, $file_list_data);

		$montage_cmd = "montage @".$file_list_path." -tile 26x13 -geometry 500x500+0+0 -quality 100 stl-".$idSrc.".jpg";
		exec($montage_cmd);		
		unlink($file_list_path);
	}

	public function printDebug() {
		$this->makeImgList();
		$this->op_url_list->printDebug();

		$this->op_id->printDebug();
	}
}

class OpUrlList {
	var $url_list;

	public function OpUrlList() {
		$this->url_list = array();
	}

	public function clear() {
		unset($this->url_list);
		$this->url_list = array();
	}

	public function addUrl($url, $file) {
		$item = array();
		$item[] = $url;
		$item[] = $file;
		$this->url_list[] = $item;
	}

	public function downloadAria2c() {
		$path = $this->makeTmpPath().".txt";
		file_put_contents($path, $this->makeAria2cList());
		exec("aria2c -i \"".$path."\"");
		unlink($path);
	}

	private function makeAria2cList() {
		$res = "";
		foreach ($this->url_list as $item) {
			$res .= $item[0]."\n";
			$res .= " out=".$item[1]."\n";
		}
		return $res;
	}

	private function makeTmpPath() {
		return dirname(__FILE__)."/tmp-".hash('ripemd160', microtime().mt_rand(1, 100000));
	}

	public function removeFiles() {
		foreach ($this->url_list as $item) {
			unlink($item[1]);
		}
	}

	public function printDebug() {
		foreach ($this->url_list as $item) {
			echo("URL ".$item[0]." File ".$item[1]."\n");
		}
	}
}

class OpId {
	var $id_src;
	var $id_op;

	public function OpId($id) {
		$this->id_src = $id;
		$this->id_op = $this->makeId($id);
	}

	private function makeId($id) {
		$id_hash = hash('ripemd160', $id);
		$id_crc = hash('crc32b', $id_hash);
		return "op".$id_hash.$id_crc;
	}

	public function getIdSrc() {
		return $this->id_src;
	}

	public function getIdOp() {
		return $this->id_op;
	}

	public function printDebug() {
		echo("id src: ".$this->id_src."\n");
		echo("id op: ".$this->id_op."\n");
	}
}

?>
