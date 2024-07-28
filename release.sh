#!/bin/bash

# Will send a curl request to the specified URL with the specified data and file.
set -eo

# Setup default values.
API_URL="${SITE_URL}/edd-api/release-download"
VERSION="${VERSION:-${GITHUB_REF#refs/tags/}}"; VERSION="${VERSION#v}"
SLUG="${SLUG:-${GITHUB_REPOSITORY#*/}}"
BUILD_DIR="${HOME}/build-${SLUG}"
CHANGELOG=""

# Ensure the required environment variables are set.
for var in SITE_URL API_KEY API_TOKEN ITEM_ID VERSION; do
    if [ -z "${!var}" ]; then
        echo "$var is not set, exiting..."
        exit 1
    fi
done

#if changelog.txt exists, read it and store it in the CHANGELOG variable.
if [[ -f "${GITHUB_WORKSPACE}/changelog.txt" ]]; then
	CHANGELOG=$(<"${GITHUB_WORKSPACE}/changelog.txt")
fi

# Output the download slug
echo "ℹ︎ SLUG is $SLUG"

# Output the download version
echo "ℹ︎ VERSION is $VERSION"

# Output the download build directory
echo "ℹ︎ BUILD_DIR is $BUILD_DIR"

# Copy the file to the build directory
echo "➤ Copying files..."
if [[ -e "$GITHUB_WORKSPACE/.distignore" ]]; then
	echo "ℹ︎ Using .distignore"
	rsync -rc --exclude-from="$GITHUB_WORKSPACE/.distignore" "$GITHUB_WORKSPACE/" "$BUILD_DIR" --delete --delete-excluded
else
	rsync -rc "$GITHUB_WORKSPACE/" "$BUILD_DIR" --delete --delete-excluded
fi

# Zipping files
echo "➤ Zipping files..."
# use a symbolic link so the directory in the zip matches the file name
ln -s "$BUILD_DIR" "${GITHUB_WORKSPACE}/${SLUG}"
zip -r "${GITHUB_WORKSPACE}/${SLUG}.zip" "$SLUG"
unlink "${GITHUB_WORKSPACE}/${SLUG}"
echo "zip_path=${GITHUB_WORKSPACE}/${SLUG}.zip" >> "${GITHUB_OUTPUT}"
echo "✓ Zip file generated!"

# Release the download
echo "➤ Releasing download..."
curl -X POST "$API_URL" \
	-F "key=$API_KEY" \
	-F "token=$API_TOKEN" \
	-F "item_id=$ITEM_ID" \
	-F "version=$VERSION" \
	-F "changelog=$CHANGELOG" \
	-F "file=@${GITHUB_WORKSPACE}/${SLUG}.zip"

# add an empty line.
echo ""

# Check if the release was successful.
if [ $? -eq 0 ]; then
	echo "✓ Download released!"
else
	echo "✗ Failed to release download!"
	exit 1
fi

echo "✓ Done!"
