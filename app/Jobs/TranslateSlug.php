<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Topic;
use App\Handlers\SlugTranslateHandler;

class TranslateSlug implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $topic;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Topic $topic)
    {
        //队列任务构造器中接收了Eloquent 模型,将会只序列化模型的ID
        $this->topic = $topic;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //请求百度API接口进行翻译
        $slug = app(SlugTranslateHandler::class)->translate($this->topic->title);

        // 修复edit或者编辑的时候会跑到路由后面的问题
        // @url https://learnku.com/laravel/t/14584/slug-has-bug?#reply76507
        if (trim($slug) === 'edit') {
            $slug = 'edit-slug';
        }

        //为了避免模型监控器循环调用,我们使用DB类直接对数据库进行操作
        \DB::table('topics')->where('id',$this->topic->id)->update(['slug'=>$slug]);
    }
}
