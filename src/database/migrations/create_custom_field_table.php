<?php

namespace Qmrp\CustomField\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomFieldTable extends Migration
{
    public function up()
    {
        Schema::create('custom_module_fields', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50)->default('')->comment('模块');
            $table->string('key', 50)->comment('字段key');
            $table->string('name', 50)->comment('字段名称');
            $table->string('type', 20)->comment('字段类型');
            $table->json('config')->comment('字段配置');
            $table->boolean('is_fixed')->default(false)->comment('是否固定列');
            $table->integer('sort_order')->default(0)->comment('排序顺序');
            $table->boolean('is_active')->default(true)->comment('是否激活');
            $table->timestamps();

            $table->unique(['module', 'key']);
            $table->index(['module', 'is_active', 'sort_order']);
        });

        Schema::create('custom_fields_user_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50)->default('')->comment('模块');
            $table->integer('custom_module_field_id')->comment('自定义模块字段ID');
            $table->foreign('custom_module_field_id')->references('id')->on('custom_module_fields')->onDelete('cascade');
            $table->integer('user_id')->comment('用户ID');
            $table->integer('sort_order')->default(0)->comment('用户自定义排序权重');
            $table->boolean('is_show')->default(true)->comment('是否显示');
            $table->boolean('is_fixed')->default(false)->comment('是否固定列');
            $table->timestamps();

            $table->unique(['module', 'custom_module_field_id', 'user_id']);
            $table->index(['module', 'user_id', 'is_show']);
        });

        Schema::create('custom_field_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('模板名称');
            $table->string('description')->nullable()->comment('模板描述');
            $table->string('module', 50)->comment('模块');
            $table->json('fields')->comment('字段配置');
            $table->boolean('is_public')->default(false)->comment('是否公开');
            $table->integer('created_by')->nullable()->comment('创建者ID');
            $table->timestamps();

            $table->index(['module', 'is_public']);
        });

        Schema::create('custom_field_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50)->comment('模块');
            $table->integer('custom_module_field_id')->comment('自定义模块字段ID');
            $table->foreign('custom_module_field_id')->references('id')->on('custom_module_fields')->onDelete('cascade');
            $table->string('permission_type', 20)->comment('权限类型：view, edit, delete');
            $table->integer('role_id')->nullable()->comment('角色ID');
            $table->integer('user_id')->nullable()->comment('用户ID');
            $table->boolean('is_allowed')->default(true)->comment('是否允许');
            $table->timestamps();

            $table->unique(['custom_module_field_id', 'permission_type', 'role_id', 'user_id']);
            $table->index(['module', 'permission_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_field_permissions');
        Schema::dropIfExists('custom_field_templates');
        Schema::dropIfExists('custom_fields_user_settings');
        Schema::dropIfExists('custom_module_fields');
    }
}
