export interface ICampaignSequence {
  countItems: number;
  countSubscribers: number;
  ctr: number;
  id: number;
  openRate: number;
  title: string;
}

export interface ICampaignWidget {
  clicked: number;
  delivered: number;
  flow: any;
  id: number;
  name: string;
  opened: number;
  optIn: number;
  sent: number;
  shows: number;
  status: boolean;
  type: number;
}
