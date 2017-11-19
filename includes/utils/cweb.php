<?php

//region Objects
class CwebObject implements JsonSerializable
{
    /**
     * Array of parameter's names.
     *
     * @var string[]
     */
    private $parameters;

    /**
     * Check wheter variable is a class parameter.
     *
     * @param object $var
     * @return boolean
     */
    protected function isParameter($var)
    {
        return in_array($var, $this->parameters);
    }

    /**
     * Serialize class parameters into associative array.
     *
     * @return string[string]
     */
    protected function getParameters()
    {
        foreach ($this->parameters as $param) {
            $value = $this->$param;
            if (isset($value)) {
                $arr[$param] = $value;
            }
        }

        return $arr;
    }

    /**
     * Costruct object using provided data.
     *
     * @param array $blob Associative array containing values for the parameters.
     * @return void
     */
    public function __construct($blob)
    {
        $reflect = new ReflectionClass($this);
        $vars = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        $getName = function ($p) {
            return $p->getName();
        };

        $this->parameters = array_map($getName, $vars);

        foreach ($blob as $key => $value) {
            if ($this->isParameter($key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Serialize object into JSON.
     *
     * @return string[string]
     */
    public function jsonSerialize()
    {
        return $this->getParameters();
    }
}

/**
 * Provides informations about an author.
 */
class Author extends CwebObject
{
    /**
     * The name of the author.
     *
     * @var string
     */
    public $name;

    /**
     * The type of the author.
     *
     * Supported values are:
     *  - company
     *  - person
     *  - organization
     *  - publisher
     *
     * @var string|null
     */
    public $type;

    /**
     * The url to author's website.
     *
     * @var string|null
     */
    public $url;

    /**
     * The age of the author.
     *
     * @var integer|null
     */
    public $age;

    /**
     * The gender of the author.
     *
     * Supported values are:
     *  - male
     *  - female
     *  - other
     *
     * @var string|null
     */
    public $gender;
}

/**
 * Used to define cache rules for a specific file or content.
 */
class Cache extends CwebObject
{
    /**
     * If the file/content is cacheable or not.
     *
     * @var bool|null
     */
    public $cacheable;

    /**
     * The expire time in milliseconds from the moment when the file has been cached.
     *
     * @var integer|null
     */
    public $expiresAfter;
}

/**
 * Defines a different version, which usally references a different resolution or quality, of a media content, such as a photo, video or audio file.
 *
 * It's used to deliver the same content optimized for different devices, like smartphones, tablets and computers, according to the client's display resolution.
 */
class MediaVersion extends CwebObject
{
    /**
     * The url of the media.
     *
     * @var string
     */
    public $url;

    /**
     * The width of the media.
     *
     * Not necessary for audio files.
     *
     * If no width is specified, Connected Web parsers will maybe not include the media on some devices.
     *
     * @var integer|null
     */
    public $width;

    /**
     * The height of the media.
     *
     * Not necessary for audio files.
     *
     * If no width is specified, Connected Web parsers will maybe not include the media on some devices.
     *
     * @var integer|null
     */
    public $height;

    /**
     * The mime type of the media.
     *
     * If no type is specified, it should be considered the same mime type of the parent object.
     *
     * @var string|null
     */
    public $type;

    /**
     * The size of the media, in bytes.
     *
     * If no size is specified, Connected Web parsers will maybe not include the media on some devices to save data.
     *
     * @var integer|null
     */
    public $size;
}

/**
 * Defines an audio file to be reproduces considering multiple bitrates and formats for different devices.
 */
class AudioObject extends CwebObject
{

    /**
     * The url of the audio file.
     *
     * @var string
     */
    public $url;


    /**
     * The bit rate of the audio file in kbps.
     *
     * If no bit rate is specified, Connected Web parsers will maybe not include the audio on mobile devices.
     *
     * @var integer|null
     */
    public $bitrate;

    /**
     * The mime type of the audio file.
     *
     * If no type is specified, Connected Web parsers will maybe not include the audio file.
     *
     * Mime type helps Connected Web readers to decide which kind of player is necessary to reproduce the audio file.
     *
     * @var string|null
     */
    public $type;

    /**
     * The size of the audio file, in bytes.
     *
     * If no size is specified, Connected Web readers will maybe not include the audio on mobile devices to save data.
     *
     * @var integer|null
     */
    public $size;

    /**
     * The plain text title of the audio file.
     *
     * @var string|null
     */
    public $title;

    /**
     * The author text of the audio file.
     *
     * This parameter requires a Connected Web Author Object.
     * @see Author
     *
     * @var Author|null
     */
    public $author;

    /**
     * The plain text description of the audio file.
     *
     * @var string|null
     */
    public $description;

    /**
     * The audio file available in other bitrates.
     *
     * If the audio file is already available in other bit rates, it's possibile to specify them in an associative array of versions. Each key of the array is the bit rate of the compressed audio file.
     *
     * This parameter requires a Connected Web Media Versions Object.
     * @see MediaVersion
     *
     * @var MediaVersion[integer]|null
     */
    public $bitrates;
}

/**
 * Defines a video to be rendered considering multiple resolutions and formats for different screens and devices.
 */
class VideoObject extends CwebObject
{
    /**
     * The url of the video.
     *
     * @var string
     */
    public $url;

    /**
     * The width of the video.
     *
     * If no width is specified, Connected Web parsers will maybe not include the video on mobile devices.
     *
     * @var integer|null
     */
    public $width;

    /**
     * The height of the video.
     *
     * If no height is specified, Connected Web parsers will maybe not include the video on mobile devices.
     *
     * @var integer|null
     */
    public $height;

    /**
     * The mime type of the video.
     *
     * If no type is specified, Connected Web parsers will maybe not include the video.
     *
     * Mime type helps Connected Web readers to decide which kind of player is necessary to reproduce the video.
     *
     * @var string|null
     */
    public $type;

    /**
     * The size of the video, in bytes.
     *
     * If no size is specified, Connected Web readers will maybe not include the video on mobile devices to save data.
     *
     * @var integer|null
     */
    public $size;

    /**
     * The plain text title of the video.
     *
     * @var string|null
     */
    public $title;

    /**
     * The author text of the video file.
     *
     * This parameter requires a Connected Web Author Object.
     * @see Author
     *
     * @var Author|null
     */
    public $author;

    /**
     * The plain text description of the video.
     *
     * @var string|null
     */
    public $description;

    /**
     * The video file available in other resolutions.
     *
     * If other resolutions of the video are already available, it's possibile to specify them in an associative array of versions. Each key of the array is the width of the resized video.
     *
     * This parameter requires a Connected Web Media Versions Object.
     * @see MediaVersion
     *
     * @var MediaVersion[integer]|null
     */
    public $resolutions;
}

class ImageObject extends CwebObject
{
    /**
     * The url of the image.
     *
     * Always specify the url of the image with the highest available resolution.
     *
     * @var string
     */
    public $url;

    /**
     * The width of the image.
     *
     * If no width is specified, Connected Web parsers will maybe render the image pixeled or even don't render it.
     *
     * @var integer|null
     */
    public $width;

    /**
     * The height of the image.
     *
     * If no height is specified, Connected Web parsers will maybe render the image pixeled or even don't render it.
     *
     * @var integer|null
     */
    public $height;

    /**
     * The mime type of the image.
     *
     * Mime type will help Connected Web parsers to decide how to render the image, for instance maybe to prevent to render multiple gif images at once.
     *
     * @var string|null
     */
    public $type;

    /**
     * The size of the image, in bytes.
     *
     * Can be used to estimate the time necessary to download the image.
     *
     * @var integer|null
     */
    public $size;

    /**
     * The plain text caption of the image.
     *
     * @var string|null
     */
    public $caption;

    /**
     * Allows to store the image regardless of the original one has been removed or not.
     *
     * @var boolean|null
     */
    public $allow_storage;

    /**
     * Other resolutions of the image.
     *
     * If other resolutions of the image are already available, it's possibile to specify them in an associative array of resolutions. Each key of the array is the width of the resized image.
     *
     * This parameter requires a Connected Web Media Versions Object.
     * @see MediaVersion
     *
     * @var MediaVersion[integer]|null
     */
    public $resolutions;
}
//endregion

//region Body Elements
class CwebElement extends CwebObject
{
    /**
     * Element type
     *
     * @var string
     */
    protected $etype;

    /**
     * Serialize object into JSON.
     *
     * @return string[string]
     */
    public function jsonSerialize()
    {
        return [
            'type' => $this->etype,
            'data' => $this->getParameters()
        ];
    }
}

/**
 * Renders an Ad static banner.
 */
class Advertising extends CwebElement
{
    /**
     * The type of banner to display.
     *
     * Supported types:
     *   - **compact**:               300 x 150 px / jpg.
     *   - **extended_compact**:      320 x 280 px / jpg.
     *   - **square**:                250 x 250 px / jpg.
     *   - **leaderboard**:           600 x 100 px / jpg.
     *   - **extended_leaderboard**:  600 x 150 px / jpg.
     *
     * @var string
     */
    public $type = "compact";

    /**
     * The image to show as the Ad banner.
     *
     * To support HDPI screen, images should be included at twice the specified resolution.
     *
     * This parameter requires a Connected Web Image Object.
     * @see ImageObject
     *
     * @var ImageObject
     */
    public $img;

    /**
     * The url to show once the user has clicked on the banner.
     *
     * @var string
     */
    public $url;

    /**
     * A caption/slogan to show with the banner.
     *
     * @var string
     */
    public $caption;

    /**
     * A list of tags to help to understand to which kind of people the Ad is targeted.
     *
     * @var string[]|null
    */
    public $targets;

    /**
     * Allows to hide the Ad to interest-unmatched users.
     *
     * If semantic_hide is set to true, Connected Web parsers are allowed to hide the Ad to interest-unmatched users in order to remove clutters. This parameter will be avluated only if the targets parameter is set.
     *
     * @var boolean|null
     */
    public $semantic_hide;

    public function __construct($blob)
    {
        parent::__construct($blob);
        $this->etype = "advertising";
    }
}

/**
 * Renders an image gallery.
 */
class Gallery extends CwebElement
{
    /**
     * The list of images to show in the gallery.
     *
     * This parameter requires a Connected Web Image Object.
     * @see ImageObject
     *
     * @var ImageObject[]
     */
    public $images = [];

    public function __construct($blob)
    {
        parent::__construct($blob);
        $this->etype = "gallery";
    }
}

/**
 * Renders a map and or the preview of a specific location (like the street view).
 */
class Location extends CwebElement
{
    /**
     * The latitude of the pointer.
     *
     * @var float
     */
    public $lat;

    /**
     * The longitude of the pointer.
     *
     * @var float
     */
    public $lng;

    /**
     * The name of the location.
     *
     * @var string|null
     */
    public $name;

    public function __construct($blob)
    {
        parent::__construct($blob);
        $this->etype = "location";
    }
}

/**
 * Renders a file.
 */
class File extends CwebElement
{

    /**
     * The url of the file.
     *
     * @var string
     */
    public $url;

    /**
     * The name of the file.
     *
     * @var string|null
     */
    public $name;

    /**
     * The mime type of the file.
     *
     * @var string|null
     */
    public $type;

    /**
     * The size of the the file, in bytes.
     *
     * Can be used to estimate the time necessary to download the file.
     *
     * @var integer|null
     */
    public $size;

    /**
     * The extension of the file.
     *
     * @var string|null
     */
    public $extension;

    /**
     * The preview image of the file.
     *
     * This parameter requires a Connected Web Image Object.
     * @see ImageObject
     *
     * @var ImageObject|null
     */
    public $img;

    public function __construct($blob)
    {
        parent::__construct($blob);
        $this->etype = "file";
    }
}

/**
 * Renders a link.
 */
class Link extends CwebElement
{

    /**
     * The url or id of the link.
     *
     * When a type is specified, it's possible to indicate directly an id to display the content of that type of link instead of its url.
     *
     * For instance, for YouTube contents it can be directily specified the id of the video to display instead of the entire url of the page of the video.
     *
     * @var string
     */
    public $value;

    /**
     * The type of link.
     *
     * Supported types:
     *   - **youtube**: renders a YouTube video.
     *
     * If no type is specified, the link is parsed as a generic one.
     *
     * @var string|null
     */
    public $type;

    /**
     * The title of the link.
     *
     * @var string|null
     */
    public $title;

    /**
     * The description of the link.
     *
     * @var string|null
     */
    public $description;

    /**
     * The preview image of the link.
     *
     * This parameter requires a Connected Web Image Object.
     * @see ImageObject
     *
     * @var ImageObject|null
     */
    public $img;

    public function __construct($blob)
    {
        parent::__construct($blob);
        $this->etype = "link";
    }
}

/**
 * Renders an audio file.
 */
class Audio extends CwebElement
{

    /**
     * The audio file.
     *
     * This parameter requires a Connected Web Audio Object.
     * @see AudioObject
     *
     * @var AudioObject
     */
    public $audio;

    /**
     * The thumbnail of the audio.
     *
     * This parameter requires a Connected Web Image Object.
     * @see ImageObject
     *
     * @var ImageObject|null
     */
    public $thumbnail;

    public function __construct($blob)
    {
        parent::__construct($blob);
        $this->etype = "audio";
    }
}

/**
 * Renders a video.
 */
class Video extends CwebElement
{
    /**
     * The video.
     *
     * This parameter requires a Connected Web Video Object.
     * @see VideoObject
     *
     * @var VideoObject
     */
    public $video;

    /**
     * The thumbnail of the video.
     *
     * This parameter requires a Connected Web Image Object.
     * @see ImageObject
     *
     * @var ImageObject|null
     */
    public $thumbnail;

    public function __construct($blob)
    {
        parent::__construct($blob);
        $this->etype = "video";
    }
}

/**
 * Renders an image.
 */
class Image extends ImageObject
{
    /**
     * Serialize object into JSON.
     *
     * @return string[string]
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'image',
            'data' => $this->getParameters()
        ];
    }
}

/**
 * Renders a text.
 */
class Text extends CwebElement
{
    /**
     * HTML text.
     *
     * Supported html tags:
     *  - b
     *  - strong
     *  - br
     *  - i
     *  - a
     *  - em
     *  - ul
     *  - ol
     *  - li
     *  - strike
     *  - del
     *
     * @var string
     */
    public $value;

    /**
     * Appearance of the text.
     *
     * Supported appearance values:
     *  - **h1**: a big heading title
     *  - **h2**: a medium heading title
     *  - **h3**: a small heading title
     *  - **quote**: a quote
     *  - **code**: a code block
     *
     * @var string|null
     */
    public $appearance;

    public function __construct($blob)
    {
        parent::__construct($blob);
        $this->etype = "text";
    }
}
//endregion

//region Structure
/**
 * Contents define the main part of a {@see Feed} file. They are the articles, images, videos... included in the file.
 */
class Content extends CwebObject
{
    /**
     * The title of the content.
     *
     * For an article it is the title of the article; for an image it can be the attached comment/post with the image.
     *
     * @var string|null
     */
    public $title;

    /**
     * The publication date for the content.
     *
     * Must be an Unix timestamp - in seconds.
     *
     * @var integer
     */
    public $pubDate;

    /**
     * The author text of the content.
     *
     * This parameter requires a Connected Web Author Object.
     * @see Author
     *
     * @var Author|null
     */
    public $author;

    /**
     * The description of the content.
     *
     * For an article it is a short summary of its content.
     *
     * If left empty, some Connected Web parsers will maybe use the text of the first Text Element (if present) as description of the content.
     *
     * @var string|null
     */
    public $description;

    /**
     * The url of the content.
     *
     * To open the original content.
     *
     * @var string|null
     */
    public $url;

    /**
     * The body of the content.
     *
     * This parameter requires a Connected Web Body Element.
     * @see CwebElement
     *
     * @var CwebElement[]
     */
    public $body = array();

    /**
     * The thumbnail image of the content.
     *
     * This parameters is necessary to specify a thumbnail of, for example, an article. It's not necessary when the content is a photo or gallery. Photo or gallery data are declared using an {@see Image} or {@see Gallery} body element.
     *
     * @var ImageObject[]|null
     */
    public $img;
}

/**
 * A Connected Web Feed File is a JSON or JSONP encoded file which contains a list of contents to be distributed.
 *
 * This file has the same scope of old RSS or Atom files.
 */
class Feed extends CwebObject
{
    /**
     * The type of file.
     *
     * Must be feed.
     *
     * @var string
     */
    public $type = "feed";

    /**
     * The version of the Connected Web format.
     *
     * It helps a file parser to understand with which version of the Connected Web format should be this file parsed.
     *
     * @var float
     */
    public $cwversion = 1.1;

    /**
     * The name of the feed.
     *
     * Can be the name of the source or a specific section of it, e.g. My Website > Politics.
     *
     * It's suggested to keep it shorter than 55 characters. If longer, some Connected Web parsers will cut it.
     *
     * @var string
     */
    public $name;

    /**
     * The description of the feed.
     *
     * This value will help platforms to understand better which kind of contents does this list include.
     *
     * It's suggested to keep it shorter than 160 characters. If longer, some Connected Web parsers will cut it.
     *
     * @var string|null
     */
    public $description;

    /**
     * A list of keywords.
     *
     * This value will help platforms to understand better which kind of contents does this list include.
     *
     * It's suggested to set no more than 5 tags, otherwise some Connected Web parsers will exclude the remaining ones.
     *
     * @var string[]|null
     */
    public $keywords;

    /**
     * The url to a Connected Web Source File.
     *
     * This parameter requires a link to a Connected Web Source File.
     * @see Source
     *
     * @var string
     */
    public $source;

    /**
     * The list of contents included in the file.
     *
     * A content can be an article, a post, an image, a video and so on.
     *
     * It's suggested to include the last 50 contents of the source.
     *
     * This parameter requires a Connected Web Content.
     * @see Content
     *
     * @var Content[]
     */
    public $contents;

    /**
     * The language of the feed.
     *
     * Must meet ISO 639-1 standardized nomenclature.
     *
     * @var string
     */
    public $language;

    /**
     * The cache preferences.
     *
     * This parameter allows to take control of the contents included in the file by specifying how and if platforms are allowed to cache them.
     *
     * This parameter requires a Connected Web Cache Object.
     * @see Cache
     *
     * @var Cache|null
     */
    public $cache;
}

/**
 * A Connected Web Source File is a JSON or JSONP encoded file which contains the information of the source of a {@see Feed} file. Hence it always requires to be linked from a Feed file.
 *
 * It's necessary to deliver more accurate information about the source of a {@see Feed} file.
 */
class Source extends CwebObject
{
    /**
     * The type of file.
     *
     * Must be source.
     *
     * @var string
     */
    public $type = "source";

    /**
     * The version of the Connected Web format.
     *
     * It helps a file parser to understand with which version of the Connected Web format should be this file parsed.
     *
     * @var float
     */
    public $cwversion = 1.1;

    /**
     * The name of the source.
     *
     * Keep it shorter than 55 characters. If longer, some platforms will cut it.
     *
     * @var string
    */
    public $name;

    /**
     * The description of the source.
     *
     * This value will help platforms to understand better which kind of contents is your source producing.
     *
     * It's suggested to keep it shorter than 160 characters. If longer, some Connected Web parsers will cut it.
     *
     * @var string|null
     */
    public $description;

    /**
     * A list of keywords which identifies the source.
     *
     * This value will help platforms to understand better which kind of contents is the source producing.
     *
     * It's suggested to set no more than 5 tags, otherwise some Connected Web parsers will exclude the remaining ones.
     *
     * @var string[]|null
     */
    public $keywords;

    /**
     * The url of the homepage of the source.
     *
     * Only public http or https urls are supported.
     *
     * @var string|null
     */
    public $url;

    /**
     * The language of the source.
     *
     * Must meet ISO 639-1 standardized nomenclature.
     *
     * @var string
     */
    public $language;

    /**
     * The logo / image of the source.
     *
     * This parameter requires a Connected Web Image Object.
     * @see ImageObject
     *
     * It's suggested a minimum resolution of 2000x2000px.
     *
     * @var ImageObject|null
     */
    public $img;

    /**
     * The cover image of the source.
     *
     * This parameter requires a Connected Web Image Object.
     * @see ImageObject
     *
     * It's suggested a minimum resolution of 2000x2000px.
     *
     * @var ImageObject|null
     */
    public $cover;
}
//endregion

// $a = new File([
//     'url' => 'http://connectedweb.org',
//     'name' => 'testfile',
//     'type' => 'text/meme',
//     'size' => 420,
//     'extension' => 'mememama',
//     'img' => new ImageObject([
//         'url' => "http://flesbing.com",
//         'width' => 50,
//         'height' => 100,
//         'allow_storage' => true
//     ]),
//     'etype' => 'hackerino'
//     ]);
// echo json_encode($a);
