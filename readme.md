# Easy Digital Downloads Release Download Action

This action creates a new release on GitHub and uploads the release assets to the site. The action is designed to work
with the [Easy Digital Downloads](https://easydigitaldownloads.com/) plugin.

## Requirements

- **Install Plugin**:
  Install [EDD Release Download](edd-release-download.zip) plugin on
  your WordPress site.
- **Secrets**: You need to add the following secrets to your repository's settings under `Settings > Secrets`.
	- `SITE_URL` - The URL of your WordPress site.
	- `API_KEY` - The API key of the Easy Digital Downloads plugin. Create a new API key
	  from `Downloads > Tools > API Keys` in your WordPress admin dashboard.
	- `API_TOKEN` - The API token of the Easy Digital Downloads plugin. Create a new API token
	  from `Downloads > Tools > API Tokens` in your WordPress admin dashboard.

## Inputs

| Input       | Description                                               | Required |
|-------------|-----------------------------------------------------------|----------|
| `site_url`  | The URL of your WordPress site.                           | Yes      |
| `api_key`   | EDD API Key                                               | Yes      |
| `api_token` | EDD API Token                                             | Yes      |
| `item_id`   | ID of the item in EDD                                     | Yes      |
| `version`   | Version of the update. Defaults to the tag name.          | Yes      |
| `slug`      | Slug of the item in EDD. Defaults is the repository name. | No       |

### Outputs

| Output     | Description                         | Required |
|------------|-------------------------------------|----------|
| `zip_path` | The path to the ZIP file generated. | Yes      |

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

## Example

To get started, you will want to copy the contents of one of [these examples](examples) into `.github/workflows/deploy.yml` and push that to your repository


## License

Our GitHub Actions are available for use and remix under the MIT license.
