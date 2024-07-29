# Easy Digital Downloads - Download Deploy Action

GitHub action to deploy a download to [Easy Digital Downloads](https://easydigitaldownloads.com/) powered WordPress
site.

## Requirements

- **Install Plugin**:
  Install [EDD Download Deploy Plugin](https://github.com/sultann/edd-download-deploy-plugin) on your WordPress site.
- **Secrets**: You need to add the following secrets to your repository's settings
  under `Settings > Secrets and Variables > Actions`.
	- `EDD_KEY` - The API key of the Easy Digital Downloads plugin. Create a new API key
	  from `Downloads > Tools > API Keys` in your WordPress admin dashboard.
	- `EDD_TOKEN` - The API token of the Easy Digital Downloads plugin. Create a new API token
	  from `Downloads > Tools > API Tokens` in your WordPress admin dashboard.
	- `SLACK_WEBHOOK` - (Optional) Slack webhook URL to send notification when deployment is successful.

## Inputs

| Input           | Required | Description                                                                                                                                                                                           |
|-----------------|----------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `site_url`      | Yes      | The URL of your WordPress site.                                                                                                                                                                       |
| `api_key`       | Yes      | EDD API Key                                                                                                                                                                                           |
| `api_token`     | Yes      | EDD API Token                                                                                                                                                                                         |
| `item_id`       | Yes      | ID of the download in EDD                                                                                                                                                                             |
| `slug`          | No       | The slug of the plugin. Default is the repository name.                                                                                                                                               |
| `version`       | No       | The release version of your plugin. This is optional by default the action will use the tag name as the version.                                                                                      |
| `dry_run`       | No       | Whether to run the action in dry run mode. This is optional and defaults to `false`. If set to `true`, the action will not deploy to WordPress.org, instead outputs the files that would be deployed. |
| `slack_webhook` | No       | Slack webhook URL to send notification when deployment is successful.                                                                                                                                 |

### Outputs

| Output     | Description                                                             |
|------------|-------------------------------------------------------------------------|
| `version`  | Version number of the release, that is being used for deployment.       |
| `zip_path` | The path to the ZIP file generated. If `generate_zip` is set to `true`. |

## Excluding files from release

If there are files or directories to be excluded from release, such as tests or editor config files, they can be
specified in either a `.distignore` file.

Sample `.distignore` file:

```
/.git
/.github
/node_modules

.distignore
.gitignore
```

## Usage

```yaml
- name: Deploy to Easy Digital Downloads
  id: deploy
  uses: sultann/edd-download-deploy@master
  with:
    # Site URL of your WordPress site.
    # Required.
    site_url: 'https://example.com'

	# EDD API Key. Generate a new API key from Downloads > Tools > API Keys in your WordPress admin dashboard.
	# Required.
    api_key: ${{ secrets.EDD_KEY }}

	# EDD API Key. Generate a new API key from Downloads > Tools > API Keys in your WordPress admin dashboard.
	# Required.
    api_token: ${{ secrets.EDD_TOKEN }}

	# The name of the zip file to be uploaded. This is optional and defaults to the repository name.
	# Optional.
    slug: 'my-plugin-slug'

    # Version of the release. Defaults to the release tag if found otherwise version from the package.json file.
    # Optional.
    version: '1.0.0'

    # Whether to run the action in dry run mode. Defaults to false. If this is set to true, the action will not deploy, instead outputs the files that would be deployed.
    # Optional.
    dry_run: true

    # Slack webhook URL to send notification when deployment is successful.
    # Optional.
    slack_webhook: ${{ secrets.SLACK_WEBHOOK }}

```
## Example

Create a new file in your repository at `.github/workflows/deploy.yml` with the following contents:

```yaml
name: Build and Deploy
on:
  push:
    tags:
      - "*"
jobs:
  build:
    name: Build release and deploy
    runs-on: ubuntu-latest
    steps:
        - name: Checkout code
          uses: actions/checkout@v2
        - name: Build & Deploy
          uses: sultann/edd-download-deploy@master
          with:
          site_url: 'https://example.com'
          api_key: ${{ secrets.EDD_KEY }}
		  api_token: ${{ secrets.EDD_TOKEN }}
		  item_id: 123
		  slack_webhook: ${{ secrets.SLACK_WEBHOOK }}
```
When a new tag is pushed to the repository, the action will build the release and deploy it to your site.

## License

Our GitHub Actions are available for use and remix under the MIT license.
