#!/bin/bash

rsvg-convert -w 88 -o velo-icons-all-24.png velo-icons-all.svg
rsvg-convert -w 176 -o velo-icons-all-48.png velo-icons-all.svg


NAMES='icon-names.txt'
PADDING=8
SIZE=24
DST='images/icons'

mkdir -p $DST

for mult in 1 2; do
	((size = SIZE * mult))
	((padding = PADDING * mult))
	SOURCE="velo-icons-all-$size.png"

	x=0
	for color in dark light transparent; do
		y=0
		cat "$NAMES" |  while read name; do
			convert -crop ${size}x${size}+$x+$y "$SOURCE" $DST/$name-$color-$size.png
			((y = y + size + padding))
		done
		((x = x + size + padding))
	done
done
