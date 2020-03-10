import { Component, EventEmitter, Input, OnChanges, OnInit, Output } from '@angular/core';
import { EmojiPickerService } from './emoji-picker.service';
const PARSE_REGEX = /:([a-zA-Z0-9_\-\+]+):/g;

@Component({
  selector: 'app-emoji-picker',
  templateUrl: './emoji-picker.component.html',
  styleUrls: ['./emoji-picker.component.scss']
})
export class EmojiPickerComponent implements OnInit, OnChanges {

  public input: string;
  public filterEmojis: string;
  public allEmojis: Array<any>;
  public popupOpen = false;

  @Input() popupAnchor = 'top';
  @Input() model: any;
  @Output() modelChange: any = new EventEmitter();
  @Input() onEnter: Function = () => {};

  constructor(
    private _emojiService: EmojiPickerService
  ) { }

  ngOnInit() {
    this.input = '';
    this.filterEmojis = '';
    this.allEmojis = this.getAll();
  }

  ngOnChanges() {
    if (this.model !== this.input) {
      this.input = this.model;
    }
  }

  public togglePopup() {
    this.popupOpen = !this.popupOpen;
  }

  public getFilteredEmojis() {
    return this.allEmojis.filter((e) => {
      if (this.filterEmojis === '') {
        return true;
      } else {
        for (const alias of e.aliases) {
          if (alias.includes(this.filterEmojis)) {
            return true;
          }
        }
      }
      return false;
    });
  }

  public onEmojiClick(e) {
    // this.input = this.input + e;
    this.modelChange.emit(e);
    // this.popupOpen = false;
  }

  public onChange(newValue) {
    this.input = this.emojify(newValue);
    this.model = this.input;
    this.modelChange.emit(this.input);
  }


  public get(emoji) {
    for (const data of this._emojiService.emojiData) {
      for (const e of data.aliases) {
        if (emoji === e) {
          return data['emoji'];
        }
      }
    }
    return emoji;
  }

  public getAll() {
    return this._emojiService.emojiData;
  }

  public emojify(str) {
    return str.split(PARSE_REGEX).map((emoji, index) => {
      if (index % 2 === 0) { return emoji; }
      return this.get(emoji);
    }).join('');
  }
}
