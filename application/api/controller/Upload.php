<?php

namespace app\api\controller;

use app\common\model\User As userModel;
use app\common\controller\Api;
use app\common\exception\UploadException;

/**
 * 文件上传
 */
class Upload extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 文件上传
     *
     */
    public function upload()
    {
        //默认普通上传文件
        $file = $this->request->file('file');
        $type = $this->request->get('type');
        try {
            $upload = new \app\common\library\Upload($file);
            $attachment = $upload->upload();
            if($type == 'avatar'){
                $user_info = $this->auth->getUser();
                userModel::where('id',$user_info['id'])->update(['avatar'=>$attachment->url]);
            }
        } catch (UploadException $e) {
            $this->error($e->getMessage());
        }

        $this->success('请求成功',$attachment);
    }
}
