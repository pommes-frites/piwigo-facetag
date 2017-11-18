# MugShot

This is a fork of the [piwigo-facetag plugin](https://github.com/pommes-frites/piwigo-facetag) and adds a bit more functionality. The code base has been rewritten using an object literal class style and includes some new features. Please note that it is not compatible with the original.

## Support
1) Group mapping; You can specify the groups allowed to tag faces.

2) Support for deleting individual tags on an image.

3) Mass updates; No need to click the button for each tag.

4) Tag will resize/shift when new image size is selected.

5) Must be logged in to tag faces.

6) Does not require Jquery (scripts or styles) if you're into that.

## Use
MugShot works mostly the same as the original piwigo-facetag plugin. When activated, there will be a button that appears on the picture pages. The button looks like a man wearing a bowler hat. Click this button and then you can click and drag on the image to make a box around someones face. On mouseup, an input box will appear where you can type the persons name. As you type, a list of available names will appear once the number of possible matches is less than 10. You can click a name to select it or, if there is only one option, press enter. Pressing enter with the text input in focus will hide the tag but this is not necessary; Once you type the name you can immediately start selecting another face. Once you are finished, click the green "SAVE" button. To delete a tag, click the red "X" inside the tag bounding box, then click save. To modify a tag, double click the tag, edit the text, then click save. An FYI, the database trigger used in the old plugin is still there. Deleting tags will also delete the corresponding MugShot tag.

If a user does not belong to a group allowed to use MugShot, they will be able to see tags created by other users. They will have no editing capabilities and cannot create their own face tags. The script files that are loaded for these users simply do not have required functions. Furthermore, the necessary ws API functions are disabled. Users must also be logged in to create tags.

## Testing
Tested to work on the bootstrap_default and bootstrap_darkroom themes. Other themes, such as clear, elegant, dark, and sylvia are *partially* working. MugShot tags created while using the bootstrap themes will display properly in these styles. *However*, creating new MugShot tags while using these themes doesn't work as the bounding box does not align properly to the mouse. This is most likely related to how the position coordinates are calculated (relative to the image versus relative to the body).
