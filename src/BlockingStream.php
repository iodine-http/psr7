<?php

namespace Iodine\Http\Psr7;

/*
 * This file is a part of Iodine HTTP Client Library.
 *
 * Copyright (c) 2017 Paulus Gandung Prakosa (rvn.plvhx@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

use Psr\Http\Message\StreamInterface;

class BlockingStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /**
     * BlockingStream constructor.
     *
     * @param resource|null $stream
     * @throws \InvalidArgumentException if $this->stream is not a stream.
     * @throws \RuntimeException if $this->stream cannot operate in blocking I/O mode.
     */
    public function __construct($stream = null)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException("\$this->stream is not a stream handle.");
        }

        $this->stream = $stream;

        if (!stream_set_blocking($this->stream, true)) {
            throw new \RuntimeException("Unable to set \$this->stream in I/O blocking mode.");
        }

        $this->metadata = stream_get_meta_data($this->stream);
        $this->stat = fstat($this->stream);
        $this->seekable = ($this->metadata['seekable'] === 1 ? true : false);
        $this->mode = $this->metadata['mode'];
        $this->size = (int)$this->stat['size'];
    }
}