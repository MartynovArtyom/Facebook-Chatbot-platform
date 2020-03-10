export class WidgetObject implements IWidgetObject {
  public id?: number;
  public title?: string;
  public type?: string;
  public inBounds?: boolean;
  public position?: {
    x?: number,
    y?: number
  };
  public widget_content?: string[];
  constructor(data?: IWidgetObject) {
    this.id = data.id;
    this.title = data.title;
    this.type = data.type;
    this.inBounds = data.inBounds;
    this.position = data.position;
    this.widget_content = data.widget_content;
  }
}

export interface IWidgetObject {
  id?: number;
  title?: string;
  type?: string;
  inBounds?: boolean;
  position?: {
    x?: number,
    y?: number
  };
  widget_content?: string[];
}
