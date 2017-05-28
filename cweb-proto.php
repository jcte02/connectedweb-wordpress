<?php
class JsonSerialize
{
    public function serialize()
    {
        return json_encode($this->remove_empty(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    private function remove_empty()
    {
        return array_filter((array) $this, function ($val) {
            return !empty($val);
        });
    }

    private function remove_null()
    {
        return array_filter((array) $this, function ($val) {
            return !is_null($val);
        });
    }
}
/*
 * Objects
 */
class author_object
{
    public $name;
    public $type; // TODO: settings
    public $url;
    public $age;
    public $gender; // TODO: settings

    public function __construct($name, $url = null, $type = null, $age = null, $gender = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->url = $url;
        $this->age = intval($age);
        $this->gender = $gender;
    }
}

class cache_object
{
    public $cacheable;
    public $expiresAfter;

    public function __construct($cacheable = null, $expiresAfter = null)
    {
        $this->cacheable = intval($cacheable);
        $this->expiresAfter = intval($expiresAfter);
    }
}

class media_version
{
    public $url;
    public $width;
    public $height;
    public $type;
    public $size; // int, bytes

    public function __construct($url, $type = null, $size = null, $width = null, $height = null)
    {
        $this->url = $url;
        $this->width = intval($width);
        $this->height = intval($height);
        $this->type = $type;
        $this->size = intval($size);
    }
}

class audio_object
{
    public $url;
    public $bitrate; // kpbs
    public $type;
    public $size; // int, bytes
    public $title;
    public $author;
    public $description;
    public $bitrates;

    public function __construct($url, $type = null, $size = null, $bitrate = null, $title = null, $description = null, $author = null, $bitrates = array())
    {
        $this->url = $url;
        $this->bitrate = intval($bitrate);
        $this->type = $type;
        $this->size = intval($size);
        $this->title = $title;
        $this->set_author($author);
        $this->description = $description;
        $this->bitrates = is_array($bitrates) ? $bitrates : array();
    }

    public function set_author($author)
    {
        if (is_a($author, 'author_object')) {
            $this->author =  $author;
        }
        return $this;
    }

    public function add_author($name, $url = null, $type = null, $age = null, $gender = null)
    {
        return $this->set_author(new author_object($name, $url, $type, $age, $gender));
    }

    public function add_bitrate($url, $bitrate, $size = null, $type = null)
    {
        $this->bitrates[$bitrate] = new media_version($url, $type, $size);
        return $this;
    }
}

class video_object
{
    public $url;
    public $width;
    public $height;
    public $type;
    public $size;
    public $title;
    public $author;
    public $description;
    public $resolutions;

    public function __construct($url, $type = null, $width = null, $height = null, $size = null, $title = null, $description = null, $author = null, $resolutions = array())
    {
        $this->url = $url;
        $this->width = intval($width);
        $this->height = intval($height);
        $this->type = $type;
        $this->size = intval($size);
        $this->title = $title;
        $this->set_author($author);
        $this->description = $description;
        $this->resoultions = is_array($resolutions) ? $resolutions : array();
    }

    public function set_author($author)
    {
        if (is_a($author, 'author_object')) {
            $this->author =  $author;
        }
        return $this;
    }

    public function add_author($name, $url = null, $type = null, $age = null, $gender = null)
    {
        return $this->set_author(new author_object($name, $url, $type, $age, $gender));
    }

    public function add_resolution($url, $width, $height = null, $size = null, $type = null)
    {
        $this->resolutions[$width] = new media_version($url, $type, $size, $width, $height);
        return $this;
    }
}

class image_object extends JsonSerialize
{
    public $url;
    public $width;
    public $height;
    public $type;
    public $size; // int, bytes
    public $caption;
    public $allow_storage;
    public $resolutions;

    public function __construct($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = false, $resolutions = array())
    {
        $this->url = $url;
        $this->width = intval($width);
        $this->height = intval($height);
        $this->type = $type;
        $this->size = intval($size);
        $this->caption = $caption;
        $this->allow_storage = intval($allow_storage);
        $this->resolutions = is_array($resolutions) ? $resolutions : array();
    }

    public function add_resolution($url, $width, $height = null, $size = null, $type = null)
    {
        $this->resolutions[$width] = new media_version($url, $type, $size, $width, $height);
        return $this;
    }
}

/*
 * Elements data
 */
class advertising_data // class for each type
{
    public $type; // enum
    public $img;
    public $url;
    public $caption;
    public $targets;
    public $semantic_hide;

    public function __construct($type, $img, $url, $caption, $targets = null, $semantic_hide = true)
    {
        $this->type = $type;
        $this->set_image($img);
        $this->url = $url;
        $this->caption = $caption;
        $this->targets = $targets;
        $this->semantic_hide = intval($semantic_hide);
    }

    public function set_image($image)
    {
        if (is_a($image, 'image_object')) {
            $this->imag = $image;
        }
        return $this;
    }

    public function emplace_image($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->set_image(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }
}

class gallery_data
{
    public $images;

    public function __construct($images = array())
    {
        $this->images = is_array($images) ? $images : array();
    }

    public function add_image($image)
    {
        if (is_a($image, 'image_object')) {
            array_push($images, $image);
        }
        return $this;
    }

    public function emplace_image($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->add_image(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }
}

class location_data
{
    public $lat;
    public $lng;
    public $name;

    public function __construct($lat, $lng, $name = null)
    {
        $this->lat = floatval($lat);
        $this->lng = floatval($lng);
        $this->name = $name;
    }
}

class file_data
{
    public $url;
    public $name;
    public $type;
    public $size; // int, bytes
    public $extension;
    public $img;

    public function __construct($url, $name = null, $type = null, $size = null, $extension = null, $img = null)
    {
        $this->url = $url;
        $this->name = $name;
        $this->type = $type;
        $this->size = intval($size);
        $this->extension = $extension;
        $this->set_image($img);
    }

    public function set_image($image)
    {
        if (is_a($image, 'image_object')) {
            $this->imag = $image;
        }
        return $this;
    }

    public function emplace_image($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->add_image(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }
}

class link_data
{
    public $value;
    public $type;
    public $title;
    public $description;
    public $img;

    public function __construct($value, $type = null, $title = null, $description = null, $img = null)
    {
        $this->value = $value;
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->set_image($img);
    }

    public function set_image($image)
    {
        if (is_a($image, 'image_object')) {
            $this->imag = $image;
        }
        return $this;
    }

    public function emplace_image($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->add_image(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }
}

class audio_data
{
    public $audio;
    public $thumbnail;

    public function __construct($audio, $thumbnail = null)
    {
        $this->set_audio($audio);
        $this->set_thumbnail($thumbnail);
    }

    public function set_audio($audio)
    {
        if (is_a($audio, 'audio_object')) {
            $this->audio = $audio;
        }
        return $this;
    }

    public function emplace_audio($url, $type = null, $size = null, $bitrate = null, $title = null, $description = null, $author = null, $bitrates = array())
    {
        return $this->set_audio(new audio_object($url, $type, $size, $bitrate, $title, $description, $author, $bitrates));
    }

    public function set_thumbnail($thumbnail)
    {
        if (is_a($thumbnail, 'image_object')) {
            $this->thumbnail = $thumbnail;
        }
        return $this;
    }
    public function emplace_thumbnail($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->set_thumbnail(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }
}

class video_data
{
    public $video;
    public $thumbnail;

    public function __construct($video, $thumbnail = null)
    {
        $this->set_video($audio);
        $this->set_thumbnail($thumbnail);
    }

    public function set_video($video)
    {
        if (is_a($audio, 'video_object')) {
            $this->video = $video;
        }
        return $this;
    }

    public function emplace_video($url, $type = null, $width = null, $height = null, $size = null, $title = null, $description = null, $author = null, $resolutions = array())
    {
        return $this->set_video(new video_object($url, $type, $width, $height, $size, $title, $description, $author, $resolutions));
    }

    public function set_thumbnail($thumbnail)
    {
        if (is_a($thumbnail, 'image_object')) {
            $this->thumbnail = $thumbnail;
        }
        return $this;
    }
    public function emplace_thumbnail($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->set_thumbnail(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }
}

class text_data
{
    public $value;
    public $appearance;

    public function __construct($value, $appearance)
    {
        $this->value = $value;
        $this->appearance = $appearance;
    }
}

/*
 * Elements
 */
class Advertising
{
    public $type = 'advertising';
    public $data;

    public function __construct($data = null)
    {
        $this->set_data($data);
    }

    public function set_data($data)
    {
        if (is_a($data, 'advertising_data')) {
            $this->data = $data;
        }
        return $this;
    }

    public function emplace_data($type, $img, $url, $caption, $targets = null, $semantic_hide = true)
    {
        return $this->set_data(new advertising_data($type, $img, $url, $caption, $targets, $semantic_hide));
    }
}

class Gallery
{
    public $type = 'gallery';
    public $data;

    public function __construct($data = null)
    {
        $this->set_data($data);
    }
    
    public function set_data($data)
    {
        if (is_a($data, 'gallery_data')) {
            $this->data = $data;
        }
        return $this;
    }

    public function emplace_data($images = null)
    {
        return $this->set_data(new gallery_data($images));
    }

    public function add_image($image)
    {
        if (!is_null($this->data)) {
            $this->data->add_image($image);
        }
        return $this;
    }
}

class Location
{
    public $type = 'location';
    public $data;

    public function __construct($data = null)
    {
        $this->set_data($data);
    }

    public function set_data($data)
    {
        if (is_a($data, 'location_data')) {
            $this->data = $data;
        }
        return $this;
    }

    public function emplace_data($lat, $lng, $name = null)
    {
        return $this->set_data(new location_data($lat, $lng, $name));
    }
}

class File
{
    public $type = 'file';
    public $data;

    public function __construct($data = null)
    {
        $this->set_data($data);
    }

    public function set_data($data)
    {
        if (is_a($data, 'file_data')) {
            $this->data = $data;
        }
        return $this;
    }

    public function emplace_data($url, $name = null, $type = null, $size = null, $extension = null, $img = null)
    {
        return $this->set_data(new file_data($url, $name, $type, $size, $extension, $img));
    }
}

class Audio
{
    public $type = 'audio';
    public $data;

    public function __construct($data = null)
    {
        $this->set_data($data);
    }

    public function set_data($data)
    {
        if (is_a($data, 'audio_data')) {
            $this->data = $data;
        }
        return $this;
    }

    public function emplace_data($url, $type = null, $size = null, $bitrate = null, $title = null, $description = null, $author = null, $bitrates = array())
    {
        return $this->set_data(new audio_data($url, $type, $size, $bitrate, $title, $description, $author, $bitrates));
    }
}

class Video
{
    public $type = 'video';
    public $data;

    public function __construct($data = null)
    {
        $this->set_data($data);
    }

    public function set_data($data)
    {
        if (is_a($data, 'video_data')) {
            $this->data = $data;
        }
        return $this;
    }

    public function emplace_data($url, $type = null, $width = null, $height = null, $size = null, $title = null, $description = null, $author = null, $resolutions = array())
    {
        return $this->set_data(new video_data($url, $type, $width, $height, $size, $title, $description, $author, $resolutions));
    }
}

class Image
{
    public $type = 'image';
    public $data;

    public function __construct($data = null)
    {
        $this->set_data($data);
    }

    public function set_data($data)
    {
        if (is_a($data, 'image_object')) {
            $this->data = $data;
        }
        return $this;
    }

    public function emplace_data($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->add_data(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }
}

class Text
{
    public $type = 'text';
    public $data;

    public function __construct($data = null)
    {
        $this->set_data($data);
    }

    public function set_data($data)
    {
        if (is_a($data, 'text_data')) {
            $this->data = $data;
        }
        return $this;
    }

    public function emplace_data($value, $appearance)
    {
        return $this->set_data(new text_data($value, $appearance));
    }
}

/*
 * Structure
 */
class Content
{
    public $title;
    public $pubTime; // int
    public $author;
    public $description;
    public $url;
    public $body;
    public $img;

    public function __construct($title = null, $pubTime = null, $author = null, $description = null, $url = null)
    {
        $this->title = $title;
        $this->pubTime = is_null($pubTime) ? date() : $pubTime;
        $this->set_author($author);
        $this->description = $description;
        $this->url = $url;
        $this->body = array();
        $this->img = array();
    }

    public function set_author($author)
    {
        if (is_a($author, 'author_object')) {
            $this->author =  $author;
        }
        return $this;
    }

    public function add_author($name, $url = null, $type = null, $age = null, $gender = null)
    {
        return $this->set_author(new author_object($name, $url, $type, $age, $gender));
    }

    public function add_element($element)
    {
        array_push($this->body, $element);
        return $this;
    }

    public function add_thumbnail($img)
    {
        if (is_a($img, 'image_object')) {
            array_push($this->img, $img);
        }
        return $this;
    }
    public function emplace_thumbnail($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->add_thumbnail(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }
}

class Feed extends JsonSerialize
{
    public $type = 'feed';
    public $cwversion = 1.1;
    public $name;
    public $description;
    public $keywords;
    public $source;
    public $contents = array();
    public $language;
    public $cache;

    public function __construct($name, $source, $language, $description = null, $keywords = null)
    {
        $this->name = $name;
        $this->source = $source;
        $this->language = $language;
        $this->description = $description;
        $this->keywords = $keywords;
    }

    public function add_content($content)
    {
        if (is_a($content, 'Content')) {
            array_push($this->contents, $content);
        }
        return $this;
    }

    public function set_cache($cacheable, $expiresAfter)
    {
        $this->cache = new cache_object($cacheable, $expiresAfter);
        return $this;
    }
}

class Source extends JsonSerialize
{
    public $type = 'source';
    public $cwversion = 1.1;
    public $name;
    public $description;
    public $keywords;
    public $url;
    public $language;
    public $img;
    public $cover;

    public function __construct($name, $language, $description = null, $url = null, $keywords = null, $img = null, $cover = null)
    {
        $this->name = $name;
        $this->language = $language;
        $this->description = $description;
        $this->url = $url;
        $this->keywords = $keywords;
        $this->set_logo($img);
        $this->set_cover($cover);
    }

    public function set_logo($img)
    {
        if (is_a($img, 'image_object')) {
            $this->img = $img;
        }
        return $this;
    }

    public function emplace_logo($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->set_logo(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }

    public function set_cover($cover)
    {
        if (is_a($cover, 'image_object')) {
            $this->cover = $cover;
        }
        return $this;
    }

    public function emplace_cover($url, $type = null, $width = null, $height = null, $size = null, $caption = null, $allow_storage = null, $resolutions = null)
    {
        return $this->set_cover(new image_object($url, $type, $width, $height, $size, $caption, $allow_storage, $resolutions));
    }
}
