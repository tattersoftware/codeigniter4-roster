# Tatter\Roster
Bulk name lookup for database relations in CodeIgniter 4

[![](https://github.com/tattersoftware/codeigniter4-roster/workflows/PHPUnit/badge.svg)](https://github.com/tattersoftware/codeigniter4-roster/actions/workflows/test.yml)
[![](https://github.com/tattersoftware/codeigniter4-roster/workflows/PHPStan/badge.svg)](https://github.com/tattersoftware/codeigniter4-roster/actions/workflows/analyze.yml)
[![](https://github.com/tattersoftware/codeigniter4-roster/workflows/Deptrac/badge.svg)](https://github.com/tattersoftware/codeigniter4-roster/actions/workflows/inspect.yml)
[![Coverage Status](https://coveralls.io/repos/github/tattersoftware/codeigniter4-roster/badge.svg?branch=develop)](https://coveralls.io/github/tattersoftware/codeigniter4-roster?branch=develop)

## Quick Start

1. Install with Composer: `> composer require tatter/roster`
2. Create a Roster class
3. Load high-performance names: `<?= service('roster')->user(1) ?>`

## Description

`Roster` solves a common, niche problem in an elegant way: quick access to display names
for entity relations without requiring database lookup. An example... Your e-commerce app
allows users to list their own products along with their username. To display the full
product page, traditionally you would either need a database `JOIN` to fetch the usernames
along with each product, or rely on a third-party solution like Object Relation Mapping (ORM)
to load the related information. `Roster` simplifies and optimizes this by preloading batches
of object names and caching them for convenient on-the-fly access.

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
* `> composer require tatter/roster`

Or, install manually by downloading the source files and adding the directory to
`app/Config/Autoload.php`.

## Usage

The `Roster` service handles locating and interacting with your Roster classes, so all you
need to do is create some Rosters. All Rosters must meet a few criteria to be discovered:
* Rosters must extend the Base Roster (`Tatter\Roster\BaseRoster`)
* Rosters must be located in a **Rosters** folder within a namespace (e.g. `App\Rosters`)
* Rosters must be named by their lookup followed "Roster" (e.g. "CarRoster")

### BaseRoster

`BaseRoster` defines the three methods that your class must implement:
* `protected function key(): string;`
* `protected function fetchAll(): array;`
* `protected function fetch($id): ?string;`

*See the `BaseRoster` file for more details.*

### ModelRoster

Most of the time Rosters will be fetching information from the database. In order to make this
more convenient and reduce repetitive code this library comes with an intermediate support
class, `ModelRoster`. If your Roster aligns with an existing Model then simply extend the
`ModelRoster` class and supply these required fields:
* `protected $modelName;`
* `protected $field;`

### Displaying

Once your Rosters are configured, use the service with the Roster name as the method and the
ID of the item as the sole parameter:

	$userName = service('roster')->user($userId);

## Example

You are developing a blog. At the bottom of every post is a comments section where logged in
users may post replies. Being the bright developer you are, you decide to use `Tatter\Roster`
to handle the display and save on expensive database joins for every page.

First let's handle displaying the username next to each commet. You already have `UserModel`
so we can use the `ModelRoster` to make it easier. Create **app/Rosters/UserRoster.php**:
```
namespace App\Rosters;

use App\Models\UserModel;
use Tatter\Roster\ModelRoster;

class UserRoster extends ModelRoster
{
	protected $modelName = UserModel::class;
	protected $field     = 'username';
}
```

That's it! `ModelRoster` handles retrieving the values based those properties. Now in our
comment HTML block we can use the Roster service to display each username:
```
<?php foreach ($comments as $comment): ?>
<div class="comment">
    <blockquote><?= $comment->content ?></blockquote>
    <div class="comment-footer">
        Commented by <?= service('roster')->user($comment->user_id) ?>
    </div>
</div>
<?php endforeach; ?>
```

Let's do our blog tags next: under the post title we want to display each tag for this post.
Unfortunately tags are in the format "[General] Specific" so no single field will work for
the display. We can still use the `ModelRoster` but instead specifying the field we will
provide our own determining method. Create **app/Rosters/TagRoster.php**:
```
namespace App\Rosters;

use App\Models\TagModel;
use Tatter\Roster\ModelRoster;

class TagRoster extends ModelRoster
{
    protected $modelName = TagModel::class;

    protected function getFieldValue(array $row): string
    {
        // Convert the database row from TagModel into its displayable form
        $general  = $row['general'];
        $specific = $row['specific'];

        return "[$general] $specific";
    }
}
```
Now our blog post header looks much cleaner:
```
<h1><?= $post->title ?></h1>
<div class="tags">
    <?php foreach ($post->tags as $tagId): ?>
    <span class="tag"><?= service('roster')->tag($tagId) ?></span>
    <?php endforeach; ?>
</div>
```

Finally, our blog is going to display a sidebar menu with post-relevant links to partners.
This data will come from a third-party API, which would be an expensive call to make on every
page load so we create a Roster for it. Because the data source is not a Model we need to make
our own extension of the Base Roster. Create **app/Rosters/LinkRoster.php**:
```
namespace App\Rosters;

use App\Libraries\LinkApi;
use Tatter\Roster\BaseRoster;

class LinkRoster extends BaseRoster
{
    /**
     * Returns the handler-specific identifier used for caching
     */
    protected function key(): string
    {
        return 'roster-links';
    }

    /**
     * Loads all IDs and their names from the data source.
     */
    protected function fetchAll(): array
    {
        $results = [];
        $links   = new LinkApi();

        foreach ($links->list() as $link) {
            $results[$link->uid] = $link->href;
        }

        return $results;
    }

    /**
     * Loads a single ID and name from the data source.
     */
    protected function fetch($id): ?string
    {
        $links = new LinkApi();

        if ($link = $links->get($id)) {
            return $link->href;
        }

        return null;
    }
}
```
A little bit more code, but using `BaseRoster` gives a lot more control about where the data
comes from and how it is formatted. You've probably already figure this part out, but let's
finish off our links with their HTML menu:
```
<nav class="links-menu">
    <h3>Visit our partner blogs!</h3>
    <ul>
        <?php foreach ($post->partnerLinks as $uid): ?>
        <span class="tag"><?= service('roster')->link($uid) ?></span>
        <?php endforeach; ?>
    </ul>
</nav>
```
