<?php
/*
- xAuth updater script.
- Codded by @PEMapModder.
*/

echo "--- xAuth Auto-Updater ---", PHP_EOL;
$version = yaml_parse_file("phar://" . __FILE__ . "/plugin.yml")["version"];
echo "Current version: $version", PHP_EOL;
echo "Checking for new versions from GitHub...", PHP_EOL;
$ret = curlGet("https://api.github.com/repos/xxFlare/xAuth/releases", 5);
if(strlen((string) $ret) > 0){
	$data = json_decode($ret);
	if(is_array($data) and isset($data[0])){
		$first = $data[0];
		$name = $first->tag_name;
		if(version_compare($version, $name, '<') and !$version->prerelease){
			echo "Updating to version $name...", PHP_EOL;
			$assets = $first->assets;
			foreach($assets as $asset){
				if($asset->name === "xAuth.phar"){
					$phar = curlGet($asset->browser_download_url);
					file_put_contents(__FILE__, $phar);
					echo "Updated!", PHP_EOL;
					exit(0);
				}
			}
		}
	}
}
echo "Not updated.", PHP_EOL;
exit(1);

function curlGet($url, $timeout){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(["User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 xAuth-Updater"], $extraHeaders));
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int) $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
}
