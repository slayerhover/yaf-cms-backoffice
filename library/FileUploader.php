<?php
class FileUploader{
	private $_files = array();
	
	private $_count;
	
	public function __construct($cascade = false){
		if (is_array($_FILES)) {
            foreach ($_FILES as $field => $struct) {
                if (!isset($struct['error'])) { continue; }
                if (is_array($struct['error'])) {
                    $arr = array();
                    for ($i = 0; $i < count($struct['error']); $i++) {

                        if ($struct['error'][$i] != UPLOAD_ERR_NO_FILE) {
                            $arr[] =new upFile($struct, $field, $i);
                            if (!$cascade) {
                                $this->_files["{$field}{$i}"] =& $arr[count($arr) - 1];
                            }
                        }
                    }
                    if ($cascade) {
                        $this->_files[$field] = $arr;
                    }
                } else {
                    if ($struct['error'] != UPLOAD_ERR_NO_FILE) {
                        $this->_files[$field] =new upFile($struct, $field);
                    }
                }
            }
        }
        $this->_count = count($this->_files);
	}
	
	public function getCount()
    {
        return $this->_count;
    }
	
	public function batchMove($destDir)
    {
        foreach ($this->_files as $file) {
            /* @var $file FLEA_Helper_FileUploader_File */
            $file->move($destDir . '/' . $file->getFilename());
        }
    }
	
	function & getFiles()
    {
        return $this->_files;
    }
	
	function & getFile($name)
    {
        if (!isset($this->_files[$name])) {
            throw new Exception("上传文件不存在");
        }
        return $this->_files[$name];
    }

}




class upFile
{

	private $_file = array();
	
	private $_name;
	
	public function __construct($struct, $name, $ix=false){
		 if ($ix !== false) {
            $s = array(
                'name' => $struct['name'][$ix],
                'type' => $struct['type'][$ix],
                'tmp_name' => $struct['tmp_name'][$ix],
                'error' => $struct['error'][$ix],
                'size' => $struct['size'][$ix],
            );
            $this->_file = $s;
        } else {
            $this->_file = $struct;
        }

        $this->_file['is_moved'] = false;
        $this->_name = $name;	
	}
	
    function getError()
    {
        return $this->_file['error'];
    }
    function isMoved()
    {
        return $this->_file['is_moved'];
    }
    function getFilename()
    {
        return $this->_file['name'];
    }
    function getSize()
    {
        return $this->_file['size'];
    }
	function getMimeType()
    {
        return $this->_file['type'];
    }
	function getTmpName()
    {
        return $this->_file['tmp_name'];
    }
	function getNewPath()
    {
        return $this->_file['new_path'];
    }
    function getExt()
    {
        if ($this->isMoved()) {
            return pathinfo($this->getNewPath(), PATHINFO_EXTENSION);
        } else {
            return pathinfo($this->getFilename(), PATHINFO_EXTENSION);
        }
    }
	function isSuccessed()
    {
        return $this->_file['error'] == UPLOAD_ERR_OK;
    }
	
	/**
     * 检查上传的文件是否成功上传，并符合检查条件（文件类型、最大尺寸）
     *
     * 文件类型以扩展名为准，多个扩展名以 , 分割，例如 .jpg,.jpeg,.png。
     *
     * @param string $allowExts 允许的扩展名
     * @param int $maxSize 允许的最大上传字节数
     *
     * @return boolean
     */
    function checkExts($allowExts = null)
    {
        if (!$this->isSuccessed()) { return false; }

        if ($allowExts) {
            if (strpos($allowExts, ',')) {
                $exts = explode(',', $allowExts);
            } elseif (strpos($allowExts, '/')) {
                $exts = explode('/', $allowExts);
            } elseif (strpos($allowExts, '|')) {
                $exts = explode('|', $allowExts);
            } else {
                $exts = array($allowExts);
            }

            $filename = $this->getFilename();
            $fileexts = explode('.', $filename);
            array_shift($fileexts);
            $count = count($fileexts);
            $passed = false;
            $exts = array_filter(array_map('trim', $exts), 'trim');
            foreach ($exts as $ext) {
                if (substr($ext, 0, 1) == '.') {
                    $ext = substr($ext, 1);
                }
                $fileExt = implode('.', array_slice($fileexts, $count - count(explode('.', $ext))));
                if (strtolower($fileExt) == strtolower($ext)) {
                    $passed = true;
                    break;
                }
            }
            if (!$passed) {
                return false;
            }
        }
        return true;
    }
	
	function checksize($maxSize = null)
    {
        if (!$this->isSuccessed()) { return false; }
        if ($maxSize && $this->getSize() > $maxSize) {
            return false;
        }

        return true;
    }
	
	function move($destPath)
    {
        $this->_file['is_moved'] = true;
        $this->_file['new_path'] = $destPath;
        return move_uploaded_file($this->_file['tmp_name'], $destPath);
    }
	
}
