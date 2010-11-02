<?php
class MediaLang
{
    // Generic
    const BACK = "Back";
    const GO = "Go";
    const SAVE = "Save";
    const RESET = "Reset";
    const DELETE = "Delete";
    const FILENAME = "File name";
    const DIR = "directory";
    const DIRNAME = "Directory name";
    const CAPTION = "Caption";
    const FILESIZE = "File size";
    const MODIFIED = "Modified";
    const ROOT = "Root";
    const PATH = "Path";

    // Admin
    const MANAGE = "Manage media";
    const DIR_NOT_WRITABLE = "Current directory (%s) is NOT writeable. You will
    need to make this directory writable to the web server in order to create
    new galleries and upload images using this interface.";
    const NEW_VERSION_AVAILABLE = "There is a new version of the Image Browser
    available for download. To download latest release
    <a href=\"http://imagebrowser.e-noise.com/download\">click here</a>.";
    const PHP_UPLOAD_DIRECTIVES = "PHP upload directives";
    const MAX_UPLOAD_SIZE = "Max. upload size";

    // Nodes
    const NODE_ERROR_DOT_FILE = "Dot files are not allowed.";
    const NODE_ERROR_INVALID_PATH = "Invalid path.";
    const NODE_ERROR_INVALID_DIR_PATH = "Path is not a directory!";

    // Dir creation
    const NEW_DIR = "Create new media directory.";
    const NEW_DIR_OK = "Directory created successfully.";
    const NEW_DIR_ERROR = "Directory could not be created.";
    const NEW_DIR_ERROR_ALREADY_EXISTS = "Directory already exists.";
    const INVALID_DIR_NAME = "Directory name not valid.";

    // Dir deletion
    const CONFIRM_DELETE = "Are you sure you want to delete ";
    const DIR_DELETE_OK = "Directory deleted successfully.";
    const DIR_DELETE_ERROR = "Directory could not be deleted. Please make sure
    that directory is empty before you try to delete it.";

    // File deletion
    const FILE_DELETE_OK = "File deleted successfully.";
    const FILE_DELETE_ERROR = "File could not be deleted.";
    const THUMB_DELETE_ERROR = "Thumbnail could not be deleted.";
    const CAPTION_DELETE_ERROR = "Caption could not be deleted.";

    // Thumb generation
    const GENERATE_THUMB = "Generate Thumbnail";
    const GENERATE_DIR_THUMBS = "Generate thumbs for all images in current directory.";
    const GENERATE_THUMB_OK = "Thumbnail generated successfully.";
    const GENERATE_THUMB_ERROR = "Could not create thumb directory in '%s'.";

    // Batch process
    const PROCESS_FORCE_MAX_DIMENSIONS = "Process images and apply maximum
    height and width as set up in Parameters window.";
    const RESIZE_IMAGES_OK = "Images resized successfully.";

    // Edit caption
    const EDIT_CAPTION = "Edit Caption";
    const EDIT_CAPTION_OK = "Caption saved successfully.";
    const EDIT_CAPTION_ERROR = "Error saving caption.";

    // Rename dir
    const RENAME = "Rename";
    const RENAME_DIR = "Rename directory";
    const RENAME_OK = "Directory successfully renamed.";
    const RENAME_ERROR = "Could not rename directory.";
    const RENAME_ERROR_SAME_NAME = "Directory name is the same. Nothing done.";

    // Upload
    const UPLOAD = "Upload";
    const UPLOAD_TITLE = "Upload single media file or ZIP archive.";
    const UPLOAD_LEGEND = "File details";
    const UPLOAD_FILE = "File";
    const UPLOAD_OK = "File uploaded successfully.";
    const UPLOAD_ERROR_NO_FILE_SELECTED = "No file selected for upload.";
    const UPLOAD_ERROR_NO_FILE_SENT = "No file sent in request.";
    const UPLOAD_ERROR_EXTRACTING_ARCHIVE = "Error extracting archive.";
}

