<?php

namespace App\Observers;

use App\Models\Topic;
use App\Handlers\SlugTranslateHandler;


// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class TopicObserver
{
    public function creating(Topic $topic)
    {
        //
    }

    public function updating(Topic $topic)
    {
        //
    }

    public function saving(Topic $topic)
    {
        //XSS 过滤
        $topic->body = clean($topic->body,'user_topic_body');

        //生成话题摘录
        $topic->excerpt = make_excerpt($topic->body);

        //如果slug字段无内容,使用翻译器对title进行翻译
        if(!$topic->slug)
        {
            $topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
            // 修复edit或者编辑的时候会跑到路由后面的问题
            // @url https://learnku.com/laravel/t/14584/slug-has-bug?#reply76507
            if (trim($topic->slug) === 'edit') {
                $topic->slug = 'edit-slug';
            }
        }
    }
}
