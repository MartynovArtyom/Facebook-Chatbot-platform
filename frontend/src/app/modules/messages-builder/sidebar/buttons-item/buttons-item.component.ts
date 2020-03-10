import { Component, Input, OnInit } from '@angular/core';
import { UUID } from 'angular2-uuid';
import { BuilderService } from '../../services/builder.service';
import { ButtonClass } from '../../builder/builder-interface';
import { BuilderFunctionsService } from '../../services/builder-functions.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ArrowsService } from '../../services/arrows.service';

@Component({
  selector: 'app-buttons-item',
  templateUrl: './buttons-item.component.html',
  styleUrls: ['./buttons-item.component.scss']
})
export class ButtonsItemComponent implements OnInit {

  @Input() config: any;
  @Input() opened: number;
  @Input() type: string;
  public listMainItems = [];
  public listMain = [];
  public modalActiveClose: any;
  public fromItem: any;

  constructor(
    public readonly _builderService: BuilderService,
    private readonly _modalService: NgbModal,
    private readonly _arrowsService: ArrowsService,
    public readonly _builderFunctionsService: BuilderFunctionsService
  ) {
    this.listMainItems = this._builderService.listMainItemsList;
    this.listMain = this._builderService.listMainItems;
  }

  ngOnInit() {
    if (this.config && this.config.length > 0) {
      this.config.forEach(button => {
        if (!button.viewSize) {
          button.viewSize = 'native';
        }
      });
    }
  }

  /**
   * Create new main item from button
   * @param step {object}
   */
  public selectedMainLink(step) {
    if (!this.fromItem.type) {
      this.fromItem.type = step.type;
    }
    this.fromItem.next_step = step.uuid;
    this.fromItem.arrow.to.id = step.uuid;
    this.fromItem.arrow.to.toItemX = step.x;
    this.fromItem.arrow.to.toItemY = step.y;
    this.fromItem.arrow.from.id = this.fromItem.uuid;
    if (this.fromItem.hasOwnProperty('x') && this.fromItem.hasOwnProperty('y')) {
      this.fromItem.arrow.from.fromItemX = this.fromItem.x + 400;
      this.fromItem.arrow.from.fromItemY = this.fromItem.y;
    }
    this._arrowsService.linksArray.push({
      to: step.uuid,
      toArr: [{
        fromObj: this.fromItem.arrow.from,
        toObj: this.fromItem.arrow.to
      }]
    });
    this.modalActiveClose.dismiss();
    this._builderService.updateDraftItem();
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCenterFlow(content, item) {
    this._builderService.selectedNextStep = [];
    this.fromItem = item;
    this.outSelectStepsItems();
    this.modalActiveClose = this._modalService.open(content);
  }

  /**
   * For steps items
   */
  public outSelectStepsItems() {
    this._builderService.requestDataItems.forEach((data) => {
      if (data.uuid !== this._builderService.config.uuid) {
        this._builderService.selectedNextStep.push({
          uuid: data.uuid,
          name: data.name,
          x: data.x,
          y: data.y,
          type: data.type
        });
      }
    });
  }

  /**
   * Create new button
   */
  public createNewButton() {
    const id = UUID.UUID();
    this.config.push(new ButtonClass({
      uuid: id,
      title: 'New Button #' + (this.config.length + 1),
      type: '',
      next_step: '',
      arrow: {
        from: {
          id: id,
          fromItemX: 10000,
          fromItemY: 10000
        },
        to: {
          id: null,
          toItemX: null,
          toItemY: null
        }
      }
    }));
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      this._builderService.updateDraftItem();
    }, 10);
  }

}
