<?php

namespace App\Handlers;

use Dingo\Api\Exception\ResourceException;
use Image;
use Illuminate\Http\Request;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Filesystem;

class ImageUploadSftpHandler
{
    protected $allowed_ext = ["png", "jpg", "gif", 'jpeg'];

    public function save($file, $folder = 'images/')
    {
        // 配置sftp
        $filesystem = new Filesystem(new SftpAdapter([
            'host' => config('filesystems.disks.admin.host'),
            'port' => config('filesystems.disks.admin.port'),
            'username' => config('filesystems.disks.admin.username'),
            'password' => config('filesystems.disks.admin.password'),
            'privateKey' => config('filesystems.disks.admin.privateKey'),
            'root' => config('filesystems.disks.admin.root') . $folder,
            'timeout' => config('filesystems.disks.admin.timeout'),
            'directoryPerm' => config('filesystems.disks.admin.directoryPerm'),
        ]));
        // 限制上传图片大小
        if ($file->getSize() > config('filesystems.disks.admin.uploadImgMaxSize')) {
            throw new ResourceException('上传图片不能大于5M');
        }
        // 限制上传图片类型
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';
        // 如果上传的不是图片将终止操作
        if (!in_array($extension, $this->allowed_ext)) {
            throw new ResourceException('只能上传图片');
        }
        // 判断目录是否存在
        $folder_name = date("Ym", time()) . '/' . date("d", time());
        if (!$filesystem->has($folder_name)) {
            if (!$filesystem->createDir($folder_name)) {
                throw new ResourceException('上传图片异常，请稍后再试');
            }
        }
        // 文件名
        $filename = $folder_name . '/' . str_random(32) . '.' . $extension;
        // 写入文件
        $file = file_get_contents($file->getRealPath());
        $result = $filesystem->put(
            $filename,
            $file
        );
        if (!$result) {
            throw new ResourceException('上传图片异常，请稍后再试');
        }
        return $folder . $filename;

    }

    public function saveStream($file, $folder = 'images/wxacode/')
    {
        // 配置sftp
        $filesystem = new Filesystem(new SftpAdapter([
            'host' => config('filesystems.disks.admin.host'),
            'port' => config('filesystems.disks.admin.port'),
            'username' => config('filesystems.disks.admin.username'),
            'password' => config('filesystems.disks.admin.password'),
            'privateKey' => config('filesystems.disks.admin.privateKey'),
            'root' => config('filesystems.disks.admin.root') . $folder,
            'timeout' => config('filesystems.disks.admin.timeout'),
            'directoryPerm' => config('filesystems.disks.admin.directoryPerm'),
        ]));
        // 限制上传图片大小
//        if ($file->getSize() > config('filesystems.disks.admin.uploadImgMaxSize')){
//            throw new ResourceException('上传图片不能大于5M');
//        }
        // 限制上传图片类型
        $extension = 'jpg';
        // 如果上传的不是图片将终止操作
        if (!in_array($extension, $this->allowed_ext)) {
            throw new ResourceException('只能上传图片');
        }
        // 判断目录是否存在
//        $folder_name = date("Ym", time()) . '/'.date("d", time());
//        if (!$filesystem->has($folder_name)){
//            if (!$filesystem->createDir($folder_name)){
//                throw new ResourceException('上传图片异常，请稍后再试');
//            }
//        }
        // 文件名
        $filename = str_random(32) . '.' . $extension;
        // 写入文件
        $result = $filesystem->put(
            $filename,
            $file
        );
        if (!$result) {
            throw new ResourceException('上传图片异常，请稍后再试');
        }
        return $folder . $filename;

    }

}