import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { Child } from '../../../builder/builder-interface';
import { BuilderFunctionsService } from '../../../services/builder-functions.service';
import { BuilderService } from '../../../services/builder.service';

@Component({
  selector: 'app-text-item',
  templateUrl: './text-item.component.html',
  styleUrls: [
    './text-item.component.scss',
    '../../../assets/general-style.scss'
  ]
})
export class TextItemComponent implements OnInit {

  @Input() item: Child;
  @Input() opened: number;
  @ViewChild('desc') public desc;

  constructor(
    public _builderFunctionsService: BuilderFunctionsService,
    public _builderService: BuilderService
  ) { }

  ngOnInit() {
  }

  public fancyCount2(str) {
    const joiner = '\u{200D}';
    const split = str.split(joiner);
    let count = 0;

    for (const s of split) {
      const num = Array.from(s.split(/[\ufe00-\ufe0f]/).join('')).length;
      count += num;
    }

    return split;
  }

  public addEmoji(oField, $event) {
    if (!oField.value || !this.item.params['description']) {
      oField.value = this.fancyCount2($event);
    } else {
      oField.value = oField.value.substring(0, oField.selectionStart) +
        this.fancyCount2($event) + oField.value.substring(oField.selectionStart, oField.value.length);
    }
    this.item.params['description'] = oField.value;
    this._builderService.updateDraftItem();
  }

  public openSubTextareaPanel(item) {
    if (this._builderService.view !== 'preview') {
      this.desc.nativeElement.focus();
      item['active'] = true;
    }
  }

  public closeSubTextareaPanel(item) {
    item['active'] = false;
    delete item['activeAction'];
    delete item['activeEmoji'];
  }

  public openEmojiPanel(value) {
    this.item.params['activeEmoji'] = value;
    delete this.item.params['activeAction'];
  }

  public openBulkActionsPanel(value) {
    this.desc.nativeElement.focus();
    this.item.params['activeAction'] = value;
    delete this.item.params['activeEmoji'];
  }

  public getCaretPos(oField, action) {
    if (!this.desc.nativeElement.value || !this.item.params['description']) {
      this.desc.nativeElement.value = action.desc;
    } else {
      this.desc.nativeElement.value = this.desc.nativeElement.value.substring(0, oField.selectionStart) +
        action.desc + this.desc.nativeElement.value.substring(oField.selectionStart, this.desc.nativeElement.value.length);
    }

    this.item.params['description'] = this.desc.nativeElement.value;
    this.closeSubTextareaPanel(this.item.params);
    setTimeout(() => {
      this.desc.nativeElement.blur();
    }, 10);
    this._builderService.updateDraftItem();
  }

}
