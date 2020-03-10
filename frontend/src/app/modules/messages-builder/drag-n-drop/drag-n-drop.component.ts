import { Component, Input, OnInit } from '@angular/core';
import { Arrow, IArrow, Item } from '../builder/builder-interface';
import { BuilderService } from '../services/builder.service';

@Component({
  template: ``
})

export class DragNDropComponent implements OnInit {

  protected startContainerPositionX = 0;
  protected startContainerPositionY = 0;
  private attachedItemArrow: IArrow;
  private startMousePositionX = 0;
  private startMousePositionY = 0;
  protected draggable = false;
  protected moving = false;
  protected isItem = false;
  @Input() scale: number;
  public positionX = 0;
  public positionY = 0;
  public count = 0;
  @Input() config: Item;
  private configMain: any;

  constructor (
    public readonly _builderService: BuilderService
  ) { }

  ngOnInit () {
    this.positionX = this.config.x;
    this.positionY = this.config.y;
  }

  public mouseDown (event: any, config: Item, data: Item[], elem: HTMLElement): void {
    this._builderService.counter = 0;
    this._builderService.prev = [];
    this.isItem = !!event.target['dataset']['drag'];
    this.startContainerPositionX = this.positionX;
    this.startContainerPositionY = this.positionY;
    this.startMousePositionX = event.clientX;
    this.startMousePositionY = event.clientY;
    this._builderService.elem = elem;
    this._builderService.scale = this.scale;
    this._builderService.config = this.config;
    this.draggable = true;
    this.moving = true;

    this._builderService.getPointsPosition(config, elem);
    this._builderService.sortOutData(data, config);
    this.configMain = JSON.parse(JSON.stringify(config));
  }

  public mouseUp (): void {
    this._builderService.checkOpenSidebar = true;
    this.attachedItemArrow = new Arrow();
    this.startMousePositionX = 0;
    this.startMousePositionY = 0;
    this.draggable = false;
    this.moving = false;
    this.isItem = false;
    if (this.configMain && this.configMain.uuid === this.config.uuid) {
      if (this.configMain.x !== this.config.x || this.configMain.y !== this.config.y) {
        this.configMain = null;
        this._builderService.updateDraftItem();
      }
    }
    if (this.count === 0) {
      if (this._builderService.obj && this._builderService.requestDataSidebar.type !== 'default' &&
        this._builderService.parentElem.uuid !== this._builderService.config.uuid) {
        this._builderService.setLink();
      } else if (this._builderService.obj && this._builderService.requestDataSidebar.type !== 'default' &&
        this._builderService.parentElem.uuid === this._builderService.config.uuid) {
        this._builderService.removeLastLink();
      }
    }
  }

  public move (event: MouseEvent, config: Item, data): void {
    if (this.moving && this.draggable && this.isItem && this._builderService.view !== 'preview') {
      this._builderService.counter++;
      const zpx = event.clientX - this.startMousePositionX;
      const zpy = event.clientY - this.startMousePositionY;
      this.positionX = this.startContainerPositionX + zpx / this.scale;
      this.positionY = this.startContainerPositionY + zpy / this.scale;
      this.config.x = this.positionX;
      this.config.y = this.positionY;

      this._builderService.openSidebar = false;
      this._builderService.requestDataSidebar = {
        type: 'default',
        name: 'Sende Nachricht',
        widget_content: []
      };

      this.moveArrow(config, this.positionX, this.positionY, data);
    }
  }

  private moveArrow (config: Item, x: number, y: number, data) {

    config.arrow.from.fromItemX = x + 400;
    config.arrow.from.fromItemY = y;
    this._builderService.forId();
    this._builderService.prev.forEach((item) => {
      item.toItemX = x;
      item.toItemY = y;
    });
  }
}


