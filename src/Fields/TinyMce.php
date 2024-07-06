<?php

declare(strict_types=1);

namespace MoonShine\TinyMce\Fields;

use MoonShine\AssetManager\Css;
use MoonShine\AssetManager\Js;
use MoonShine\UI\Fields\Textarea;

class TinyMce extends Textarea
{
    protected string $view = 'moonshine-tinymce::fields.tinymce';

    public array $plugins = [
        'anchor', 'autolink', 'autoresize', 'charmap', 'codesample', 'code', 'emoticons', 'image', 'link',
        'lists', 'advlist', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount', 'directionality',
        'fullscreen', 'help', 'nonbreaking', 'pagebreak', 'preview', 'visualblocks', 'visualchars',
    ];

    public string $menubar = 'file edit insert view format table tools';

    public string $toolbar = 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table tabledelete hr nonbreaking pagebreak | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat | codesample | ltr rtl | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | fullscreen preview print visualblocks visualchars code | help';

    public string $addedToolbar = '';

    public array $mergeTags = [];

    public string $commentAuthor = '';

    public string $locale = '';

    public array $config = [];

    public static string $token = '';

    public static string $version = '6';

    public static ?string $fileManagerUrl = null;

    public function getAssets(): array
    {
        $assets = [
            Css::make('vendor/moonshine-tinymce/tinymce.css'),
            Js::make('vendor/moonshine-tinymce/tinymce.min.js'),
            Js::make('vendor/moonshine-tinymce/tinymce-init.js'),
        ];

        if ($this->getToken()) {
            $assets[] = Js::make("https://cdn.tiny.cloud/1/{$this->getToken()}/tinymce/{$this->getVersion()}/plugins.min.js");
        }

        return $assets;
    }

    public static function token(string $token): void
    {
        self::$token = $token;
    }

    protected function getToken(): string
    {
        return self::$token;
    }

    public static function version(string $version): void
    {
        self::$token = $version;
    }

    protected function getVersion(): string
    {
        return self::$version;
    }

    public static function fileManager(string $url): void
    {
        self::$fileManagerUrl = $url;
    }

    protected function getFileManagerUrl(): ?string
    {
        return self::$fileManagerUrl;
    }

    public function mergeTags(array $mergeTags): self
    {
        $this->mergeTags = $mergeTags;

        return $this;
    }

    public function commentAuthor(string $commentAuthor): self
    {
        $this->commentAuthor = $commentAuthor;

        return $this;
    }

    public function plugins(string|array $plugins): self
    {
        if (is_string($plugins)) {
            $plugins = explode(' ', $plugins);
        }

        $this->plugins = $plugins;

        return $this;
    }

    public function getPlugins(): array
    {
        $plugins = $this->plugins;

        return collect($plugins)->unique()->toArray();
    }

    public function menubar(string $menubar): self
    {
        $this->menubar = $menubar;

        return $this;
    }

    public function toolbar(string $toolbar): self
    {
        $this->toolbar = $toolbar;

        return $this;
    }

    public function addConfig(string $name, mixed $value): self
    {
        $name = str($name)->lower()->value();

        $reservedNames = [
            'selector',
            'path_absolute',
            'file_manager',
            'relative_urls',
            'branding',
            'skin',
            'file_picker_callback',
            'language',
            'plugins',
            'menubar',
            'toolbar',
            'tinycomments_mode',
            'tinycomments_author',
            'mergetags_list',
        ];

        if (is_string($value) && str($value)->isJson()) {
            $value = json_decode($value, true);
        }

        if (! in_array($name, $reservedNames)) {
            $this->config[$name] = $value;
        }

        return $this;
    }

    public function addPlugins(string|array $plugins): self
    {
        if (is_string($plugins)) {
            $plugins = explode(' ', $plugins);
        }

        $this->plugins = array_merge($this->plugins, $plugins);

        return $this;
    }

    public function removePlugins(string|array $plugins): self
    {
        if (is_string($plugins)) {
            $plugins = explode(' ', $plugins);
        }

        $this->plugins = array_diff($this->plugins, $plugins);

        return $this;
    }

    public function addToolbar(string $toolbar): self
    {
        $this->addedToolbar = $toolbar;

        return $this;
    }

    public function getConfig(): array
    {
        return [
            'toolbar_mode' => 'sliding',
            'language' => ! empty($this->locale) ? $this->locale : app()->getLocale(),
            'plugins' => implode(' ', $this->getPlugins()),
            'menubar' => trim($this->menubar),
            'toolbar' => trim($this->toolbar . ' ' . $this->addedToolbar),
            'tinycomments_mode' => $this->commentAuthor === '' || $this->commentAuthor === '0' ? null : 'embedded',
            'tinycomments_author' => $this->commentAuthor === '' || $this->commentAuthor === '0' ? null : $this->commentAuthor,
            'mergetags_list' => $this->mergeTags === []
                ? null
                : json_encode($this->mergeTags, JSON_THROW_ON_ERROR),
            'file_manager' => $this->getFileManagerUrl(),
            ...$this->config,
        ];
    }

    public function locale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    protected function resolveValue(): string
    {
        return str($this->toValue())->replace(
            ['&amp;', '&lt;', '&gt;', '&nbsp;', '&quot;'],
            ['&amp;amp;', '&amp;lt;', '&amp;gt;', '&amp;nbsp;', '&amp;quot;']
        )->value();
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [
            'config' => json_encode($this->getConfig()),
        ];
    }
}
