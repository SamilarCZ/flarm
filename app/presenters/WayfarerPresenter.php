<?php

	namespace App\Presenters;

	use App\Model\BlocksModel;
	use App\Model\FoldersModel;
	use App\Model\PlaylistModel;
	use Nette\Database\Context;
	use Nette\DI\Container;


	/**
	 * Teve presenter. The multimedial center by Samilar
	 */
	class WayfarerPresenter extends BasePresenter {
		/**
		 * @var Container
		 */
		private $netteContainer;
		/**
		 * @var Context
		 */
		private $context;
		/**
		 * @var BlocksModel
		 */
		private $blocksModel;

		private $debug = false;
		private $debug_lite = false;
		private $debug_time_start;
		private $debug_time_end;
		private $debug_time_show;

		public function __construct(Container $container, Context $context, BlocksModel $blocksModel){
			$this->netteContainer = $container;
			$this->context = $context;
			$this->blocksModel = $blocksModel;
		}

		public function renderIndex(){
			$this->redirect('dashboard');
		}

		public function renderDashboard() {

			/*
			 * DROP TABLE IF EXISTS `blocks`;
CREATE TABLE `blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_1` int(11) DEFAULT NULL,
  `link_2` int(11) DEFAULT NULL,
  `link_3` int(11) DEFAULT NULL,
  `link_4` int(11) DEFAULT NULL,
  `link_5` int(11) DEFAULT NULL,
  `link_6` int(11) DEFAULT NULL,
  `link_7` int(11) DEFAULT NULL,
  `link_8` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `link_1` (`link_1`),
  KEY `link_2` (`link_2`),
  KEY `link_3` (`link_3`),
  KEY `link_4` (`link_4`),
  KEY `link_5` (`link_5`),
  KEY `link_6` (`link_6`),
  KEY `link_7` (`link_7`),
  KEY `link_8` (`link_8`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			 * */

			$this->debug = true;
//			$this->debug_lite = false;
//			ini_set('memory_limit', '1024M');
			set_time_limit(0);
			if($this->debug === true || $this->debug_lite === true || $this->debug_time_show === true) $this->debug_time_start = microtime(true);
			$desiredArray = [];
//			/** minimalni hodnota ! (pokud se jedna o hranici generator vygeneruje vzdy tak, aby byli pozadovane ID existujici) **/
//			$desiredLastBlockId = 10000;
//			for($i=0;$i<=($desiredLastBlockId/10000);$i++){
//				$desiredArray[] = $i*10000;
//			}
			$this->template->draw = $this->drawStoredBlock();
			if(count($desiredArray) > 0) {
				foreach ($desiredArray as $iteration) {
					$blockArray = $this->generateBlockArray($iteration, 10, 25);
					foreach ($blockArray as $row => $value) {
						foreach ($value as $col => $value2) {
							$newBlock['id'] = $value2['block'];
							if (isset($value2['links']['link_1']['block'])) $newBlock['link_1'] = $value2['links']['link_1']['block'];
							if (isset($value2['links']['link_2']['block'])) $newBlock['link_2'] = $value2['links']['link_2']['block'];
							if (isset($value2['links']['link_3']['block'])) $newBlock['link_3'] = $value2['links']['link_3']['block'];
							if (isset($value2['links']['link_4']['block'])) $newBlock['link_4'] = $value2['links']['link_4']['block'];
							if (isset($value2['links']['link_5']['block'])) $newBlock['link_5'] = $value2['links']['link_5']['block'];
							if (isset($value2['links']['link_6']['block'])) $newBlock['link_6'] = $value2['links']['link_6']['block'];
							if (isset($value2['links']['link_7']['block'])) $newBlock['link_7'] = $value2['links']['link_7']['block'];
							if (isset($value2['links']['link_8']['block'])) $newBlock['link_8'] = $value2['links']['link_8']['block'];
							$inserted = $this->blocksModel->insert($newBlock);
							echo 'inserted block ID: ' . $inserted->getPrimary() . '<br />';
							if ($inserted->getPrimary() % 100 === 0) {
								echo 'must sleep now for few seconds ...<br />';
								sleep(1);
							}
							/** DEBUG **/
							if ($this->debug || $this->debug_lite === true) {
								$memory_usage_mb = round((memory_get_usage() / 1000000), 2);
								echo 'Inserting into database consumes ' . $memory_usage_mb . ' MB usage<br />';
							}
							/** DEBUG **/
							ob_flush();
							flush();
						}
					}
					if ($this->debug === true || $this->debug_lite === true || $this->debug_time_show === true) $this->debug_time_end = microtime(true);
					if ($this->debug === true || $this->debug_lite === true || $this->debug_time_show === true) echo 'Generation of world takes : ' . ($this->debug_time_end - $this->debug_time_start) . ' sec<br />';
				}
			}
		}

		private function generateBlockArray($block_id = 1, $rows = 100, $cols = 50){
			$rowStart = 1;
			$blocksArray = [];
			$blockIterator = $block_id;
			if($block_id !== 1){
				$rowStart = ($block_id/100)+1;
				$rows = ($rowStart + $rows)-1;
				$blockIterator = $block_id+1;
			}

			/** DEBUG **/
			if($this->debug || $this->debug_lite === true){
				echo '<h1>GENERATOR SETTING : R[' . $rows . '], C[' . $cols . '], RS[' . $rowStart . ']</h1>';
			}
			/** DEBUG **/

			for($i=$rowStart;$i<=$rows;$i++) {
				for ($ii = 1; $ii <= $cols; $ii++) {
					$blocksArray[$i][$ii]['block'] = $blockIterator;
					$blockIterator++;
				}
			}
			/*
			echo 'R*C=' . ($rows * $cols) . '<br/>';
			echo 'R/C=' . ($rows / $cols) . '<br/>';
			echo 'C/R=' . ($cols / $rows) . '<br/>';
			echo $this->drawBlockArray($blocksArray);
			die;
			*/
			/** DEBUG **/
			if($this->debug || $this->debug_lite === true) {
				$memory_usage_mb = round((memory_get_usage() / 1000000), 2);
				echo '<h3>Createing of blocks consumes ' . $memory_usage_mb . ' MB usage</h3>';
			}
			/** DEBUG **/
			$blockIterator = $block_id;
			if($block_id !== 1) $blockIterator = $block_id+1;
			for($i=$rowStart;$i<=$rows;$i++){
				for($ii=1;$ii<=$cols;$ii++) {
					$links = [];
					if (isset($blocksArray[$i - 1][$ii - 1]['block'])) 	$links['link_1']['block'] = $blocksArray[$i - 1][$ii - 1]['block'];
					if (isset($blocksArray[$i - 1][  $ii  ]['block'])) 	$links['link_2']['block'] = $blocksArray[$i - 1][  $ii  ]['block'];
					if (isset($blocksArray[$i - 1][$ii + 1]['block'])) 	$links['link_3']['block'] = $blocksArray[$i - 1][$ii + 1]['block'];
					if (isset($blocksArray[  $i  ][$ii - 1]['block'])) 	$links['link_4']['block'] = $blocksArray[  $i  ][$ii - 1]['block'];
					if (isset($blocksArray[$i    ][$ii + 1]['block'])) 	$links['link_5']['block'] = $blocksArray[  $i  ][$ii + 1]['block'];
					if (isset($blocksArray[$i + 1][$ii - 1]['block'])) 	$links['link_6']['block'] = $blocksArray[$i + 1][$ii - 1]['block'];
					if (isset($blocksArray[$i + 1][  $ii  ]['block'])) 	$links['link_7']['block'] = $blocksArray[$i + 1][  $ii  ]['block'];
					if (isset($blocksArray[$i + 1][$ii + 1]['block'])) 	$links['link_8']['block'] = $blocksArray[$i + 1][$ii + 1]['block'];

					/** DEBUG **/
					if($this->debug) {
						echo '<h2>got block ' . ($blockIterator) . '(row: ' . $i . ',col: ' . $ii . ') try to search existing links ...</h2>';
						echo 'link_1 -> row : ' . ($i - 1) . ', col: ' . ($ii - 1) . ', link_block_id : ' . ($blockIterator - 101) . ' verification = ' . ((isset($blocksArray[$i - 1][$ii - 1])) ? 'TRUE' : 'FALSE') . '<br />';
						echo 'link_2 -> row : ' . ($i - 1) . ', col: ' . ($ii) . ', link_block_id : ' . ($blockIterator - 100) . ' verification = ' . ((isset($blocksArray[$i - 1][$ii])) ? 'TRUE' : 'FALSE') . '<br />';
						echo 'link_3 -> row : ' . ($i - 1) . ', col: ' . ($ii + 1) . ', link_block_id : ' . ($blockIterator - 99) . ' verification = ' . ((isset($blocksArray[$i - 1][$ii + 1])) ? 'TRUE' : 'FALSE') . '<br />';
						echo 'link_4 -> row : ' . ($i) . ', col: ' . ($ii - 1) . ', link_block_id : ' . ($blockIterator - 1) . ' verification = ' . ((isset($blocksArray[$i][$ii - 1])) ? 'TRUE' : 'FALSE') . '<br />';
						echo 'link_5 -> row : ' . ($i) . ', col: ' . ($ii + 1) . ', link_block_id : ' . ($blockIterator + 1) . ' verification = ' . ((isset($blocksArray[$i][$ii + 1])) ? 'TRUE' : 'FALSE') . '<br />';
						echo 'link_6 -> row : ' . ($i + 1) . ', col: ' . ($ii - 1) . ', link_block_id : ' . ($blockIterator + 99) . ' verification = ' . ((isset($blocksArray[$i + 1][$ii - 1])) ? 'TRUE' : 'FALSE') . '<br />';
						echo 'link_7 -> row : ' . ($i + 1) . ', col: ' . ($ii) . ', link_block_id : ' . ($blockIterator + 100) . ' verification = ' . ((isset($blocksArray[$i + 1][$ii])) ? 'TRUE' : 'FALSE') . '<br />';
						echo 'link_8 -> row : ' . ($i + 1) . ', col: ' . ($ii + 1) . ', link_block_id : ' . ($blockIterator + 101) . ' verification = ' . ((isset($blocksArray[$i + 1][$ii + 1])) ? 'TRUE' : 'FALSE') . '<br />';
					}
					/** DEBUG **/

					$blocksArray[$i][$ii]['links'] = $links;
					$blockIterator++;
				}

				/** DEBUG **/
				if($this->debug || $this->debug_lite === true) {
					$memory_usage_mb = round((memory_get_usage() / 1000000), 2);
					echo 'Generating links between blocks consumes ' . $memory_usage_mb . ' MB usage<br />';
				}
				/** DEBUG **/

			}
			return $blocksArray;
		}

		public function drawBlockArray(array $blockArray){
			$paint = '<table>';
			$i=1;
			foreach($blockArray as $key => $val){
				$paint .= '<tr>';
				foreach($val as $key2 => $val2){
					$paint .= '<td style="border: 1px solid black; text-align: center; "><small>';
					$paint .= '(' . $i . ') ' . $key . '/' . $key2 ;
					$paint .= '</small></td>';
					$i++;
				}
				$paint .= '</tr>';
			}
			$paint .= '</table>';
			return $paint;
		}

		public function drawStoredBlock(){
			$blockModel = $this->blocksModel->getAll();
			$paint = '<table>' . PHP_EOL;
			foreach($blockModel as $key => $val){
				if($val['id'] === 1) $paint .= '<tr>' . PHP_EOL;
					$paint .= '<td style="border: 1px solid black; text-align: center; ">' .  PHP_EOL;
						$paint .= '<small>' .  PHP_EOL;
						$paint .= '[' . $val['id'] . ']' .  PHP_EOL;
						$paint .= '</small>' .  PHP_EOL;
					$paint .= '</td>' .  PHP_EOL;
					$paint .= '<td style="border: 1px solid black; text-align: center; ">' .  PHP_EOL;
						$paint .= '<table>' .  PHP_EOL;
							$paint .= '<tr style="height: 2em;">' .  PHP_EOL;
								$paint .= '<td style="width:33%;border: 1px solid black; text-align: center; ' . (($val['link_1'])?'':' background-color:red;') . '">[NE]' . (($val['link_1'])? '( : ' . $val['link_1'] . ')' : '') .'</td>' .  PHP_EOL;
								$paint .= '<td style="width:33%;border: 1px solid black; text-align: center; ' . (($val['link_2'])?'':' background-color:red;') . '">[N]' . (($val['link_2'])? '( : ' . $val['link_2'] . ')' : '') .'</td>' .  PHP_EOL;
								$paint .= '<td style="width:33%;border: 1px solid black; text-align: center; ' . (($val['link_3'])?'':' background-color:red;') . '">[NW]' . (($val['link_3'])? '( : ' . $val['link_3'] . ')' : '') .'</td>' .  PHP_EOL;
							$paint .= '</tr>' .  PHP_EOL;
							$paint .= '<tr style="height: 2em;">' .  PHP_EOL;
								$paint .= '<td style="border: 1px solid black; text-align: center; ' . (($val['link_4'])?'':' background-color:red;') . '">[E]' . (($val['link_4'])? '( : ' . $val['link_4'] . ')' : '') .'</td>' .  PHP_EOL;
								$paint .= '<td style="border: 1px solid black; text-align: center; ">[X]</td>' .  PHP_EOL;
								$paint .= '<td style="border: 1px solid black; text-align: center; ' . (($val['link_5'])?'':' background-color:red;') . '">[W]' . (($val['link_5'])? '( : ' . $val['link_5'] . ')' : '') .'</td>' .  PHP_EOL;
							$paint .= '</tr>' .  PHP_EOL;
							$paint .= '<tr style="height: 2em;">' .  PHP_EOL;
								$paint .= '<td style="border: 1px solid black; text-align: center; ' . (($val['link_6'])?'':' background-color:red;') . '">[SE]' . (($val['link_6'])? '( : ' . $val['link_6'] . ')' : '') .'</td>' .  PHP_EOL;
								$paint .= '<td style="border: 1px solid black; text-align: center; ' . (($val['link_7'])?'':' background-color:red;') . '">[S]' . (($val['link_7'])? '( : ' . $val['link_7'] . ')' : '') .'</td>' .  PHP_EOL;
								$paint .= '<td style="border: 1px solid black; text-align: center; ' . (($val['link_8'])?'':' background-color:red;') . '">[SW]' . (($val['link_8'])? '( : ' . $val['link_8'] . ')' : '') .'</td>' .  PHP_EOL;
							$paint .= '</tr>' .  PHP_EOL;
						$paint .= '</table>' .  PHP_EOL;
					$paint .= '</td>' .  PHP_EOL;
				if($val['id'] % 4 === 0) $paint .= '</tr><tr>' .  PHP_EOL;
				if($val['id'] === count($blockModel)) $paint .= '</tr>' . PHP_EOL;
			}
			$paint .= '</table>' .  PHP_EOL;

			echo $paint;
		}

	}
