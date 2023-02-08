<?php

declare(strict_types=1);

/**
 * This file is part of the guanguans/ai-commit.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

use Illuminate\Support\Str;

return [
    /**
     * Append options for the `git commit` command.
     */
    'commit_options' => [
        // '--edit',
    ],

    /**
     * Append options for the `git diff` command.
     */
    'diff_options' => [
        ':!*.lock',
    ],

    /**
     * Force no edit mode.
     */
    'no_edit' => false,

    /**
     * The prompt name.
     */
    'prompt' => 'conventional',

    /**
     * The number of generated messages.
     */
    'num' => 3,

    /**
     * The generator name.
     */
    'generator' => 'openai',

    /**
     * The mark of diff.
     */
    'diff_mark' => '<diff>',

    /**
     * The mark of number.
     */
    'num_mark' => '<num>',

    /**
     * The list of generators.
     */
    'generators' => [
        'openai' => [
            'driver' => 'openai',
            'http_options' => [
                'connect_timeout' => 3,
                'timeout' => 60,
            ],
            'api_key' => 'sk-...',
            'completion_parameters' => [
                'model' => 'text-davinci-003',
                // 'prompt' => $prompt,
                'suffix' => null,
                'max_tokens' => 500,
                'temperature' => 0.0,
                'top_p' => 1.0,
                'n' => 1,
                'stream' => true,
                'logprobs' => null,
                'echo' => false,
                'stop' => null,
                'presence_penalty' => 0,
                'frequency_penalty' => 0,
                'best_of' => 1,
                // 'logit_bias' => null,
                // 'user' => Str::uuid()->toString(),
            ],
        ],
    ],

    /**
     * The list of prompts.
     *
     * @see https://www.conventionalcommits.org
     * @see https://github.com/ahmetkca/CommitAI
     * @see https://github.com/shanginn/git-aicommit
     */
    'prompts' => [
        'conventional' => <<<'conventional'
Here is the output of the `git diff`:
<diff>
Here are some best practices for writing commit messages:
- Write clear, concise, and descriptive messages that explain the changes made in the commit.
- Use the present tense and active voice in the message, for example, "Fix bug" instead of "Fixed bug."
- Use the imperative mood, which gives the message a sense of command, e.g. "Add feature" instead of "Added feature"
- Limit the subject line to 72 characters or less.
- Capitalize the subject line.
- Do not end the subject line with a period.
- Limit the body of the message to 256 characters or less.
- Use a blank line between the subject and the body of the message.
- Use the body of the message to provide additional context or explain the reasoning behind the changes.
- Avoid using general terms like "update" or "change" in the subject line, be specific about what was updated or changed.
- Explain, What was done at a glance in the subject line, and provide additional context in the body of the message.
- Why the change was necessary in the body of the message.
- The details about what was done in the body of the message.
- Any useful details concerning the change in the body of the message.
- Use a hyphen (-) for the bullet points in the body of the message.
Write <num> commit messages that accurately summarizes the changes made in the given `git diff` output, following the best practices listed above and the conventional commit format.
Please provide a response in the form of a valid JSON object and do not include "Output:", "Response:" or anything similar to those two before it, in the following format:
[
    {
        "id": 1,
        "subject": "<type>(<scope>): <subject>",
        "body": "<BODY (bullet points)>"
    },
    ...
    {
        "id": n,
        "subject": "<type>(<scope>): <subject>",
        "body": "<BODY (bullet points)>"
    }
]
conventional
        ,
    ],
];
